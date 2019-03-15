import {Component, OnInit, ViewEncapsulation} from '@angular/core';
import {Settings} from "../../shared/settings.service";
import * as moment from 'moment';
import * as Chart from 'chart.js'
import {utils} from "../../shared/utils";
import {ActivatedRoute} from "@angular/router";

declare const gapi;

@Component({
    selector: 'analytics',
    templateUrl: './analytics.component.html',
    styleUrls: ['./analytics.component.scss'],
    encapsulation: ViewEncapsulation.None
})
export class AnalyticsComponent implements OnInit {

    /**
     * Whether analytics have already been loaded.
     */
    public analyticsLoaded = false;

    /**
     * Stats for analytics page header.
     */
    public stats = {artists: 1, albums: 1, tracks: 1, users: 1};

    /**
     * AnalyticsComponent Constructor.
     */
    constructor(
        public settings: Settings,
        private utils: utils,
        private route: ActivatedRoute
    ) {}

    ngOnInit() {
        this.route.data.subscribe(data => {
            this.stats = data.stats;
        });

        if ( ! this.analyticsLoaded) {
            (function (w, d, s, g, js, fs) {
                g = w['gapi'] || (w['gapi'] = {});
                g.analytics = {
                    q: [], ready: function (f) {
                        this.q.push(f);
                    }
                };
                js = d.createElement(s);
                fs = d.getElementsByTagName(s)[0];
                js.src = 'https://apis.google.com/js/platform.js';
                fs.parentNode.insertBefore(js, fs);
                js.onload = function () {
                    g.load('analytics');
                };
            }(window, document, 'script'));

            gapi.analytics.ready(() => {
                this.utils.loadScript(this.settings.getBaseUrl(true)+'assets/js/ViewSelector2.js').then(() => {
                    this.renderActiveUsers();
                    this.authAnalytics();
                    this.renderAnalytics();
                });

            });
        } else {
            this.renderActiveUsers();
            this.renderAnalytics();
            this.showAnalytics();
        }
    }

    /**
     * Show analytics charts and hide analytics preview image.
     */
    private showAnalytics() {
        let charts = document.querySelectorAll('.charts-row');
        for (let j = 0; j < charts.length; j++) {
            charts[j].classList.remove('hidden');
        }

        let nodes = document.querySelectorAll('#embed-api-auth-container, .unauthorized-container');
        for (let i = 0; i < nodes.length; i++) {
            nodes[i].classList.add('hidden');
        }
    }

    /**
     * Authorize the user immediately if the user has already granted access.
     * If no access has been created, render an authorize button inside the
     * element with the ID "embed-api-auth-container".
     */
    private authAnalytics() {
        gapi.analytics.auth.authorize({
            container: 'embed-api-auth-container',
            clientid: this.settings.get('analytics.google_id'),
        });
    }

    private renderAnalytics() {
        gapi.analytics.auth.on('success', () => {
            this.showAnalytics();
        });

        /**
         * Create a new ActiveUsers instance to be rendered inside of an
         * element with the id "active-users-container" and poll for changes every
         * five seconds.
         */
        let activeUsers = new gapi.analytics.ext.ActiveUsers({
            container: 'active-users-container',
            pollingInterval: 5
        });


        /**
         * Add CSS animation to visually show when users come and go.
         */
        activeUsers.once('success', function () {
            let element = this.container.firstChild;
            let timeout;

            this.on('change', function (data) {
                let element = this.container.firstChild;
                let animationClass = data.delta > 0 ? 'is-increasing' : 'is-decreasing';
                element.className += (' ' + animationClass);

                clearTimeout(timeout);
                timeout = setTimeout(function () {
                    element.className =
                        element.className.replace(/ is-(increasing|decreasing)/g, '');
                }, 3000);
            });
        });

        /**
         * Create a new ViewSelector2 instance to be rendered inside of an
         * element with the id "view-selector-container".
         */
        let viewSelector = new gapi.analytics.ext.ViewSelector2({
            container: 'view-selector-container'
        }).execute().on('error', function (e) {
            console.error(e);
        });

        /**
         * Update the activeUsers component, the Chartjs charts, and the dashboard
         * title whenever the user changes the view.
         */
        viewSelector.on('viewChange', function (data) {

            // Start tracking active users for this view.
            activeUsers.set(data).execute();

            // Render all the of charts for this view.
            renderWeekOverWeekChart(data.ids);
            renderYearOverYearChart(data.ids);
            renderTopBrowsersChart(data.ids);
            renderTopCountriesChart(data.ids);
        });

        /**
         * Draw the chart.js line chart with data from the specified view that
         * overlays session data for the current week over session data for the
         * previous week.
         */
        function renderWeekOverWeekChart(ids) {

            // Adjust `now` to experiment with different days, for testing only...
            let now = moment(); // .subtract(3, 'day');

            let thisWeek = query({
                'ids': ids,
                'dimensions': 'ga:date,ga:nthDay',
                'metrics': 'ga:sessions',
                'start-date': moment(now).subtract(1, 'day').day(0).format('YYYY-MM-DD'),
                'end-date': moment(now).format('YYYY-MM-DD')
            });

            let lastWeek = query({
                'ids': ids,
                'dimensions': 'ga:date,ga:nthDay',
                'metrics': 'ga:sessions',
                'start-date': moment(now).subtract(1, 'day').day(0).subtract(1, 'week')
                    .format('YYYY-MM-DD'),
                'end-date': moment(now).subtract(1, 'day').day(6).subtract(1, 'week')
                    .format('YYYY-MM-DD')
            });

            Promise.all([thisWeek, lastWeek]).then(function (results: any) {

                let data1 = results[0].rows.map(function (row) {
                    return +row[2];
                });
                let data2 = results[1].rows.map(function (row) {
                    return +row[2];
                });
                let labels = results[1].rows.map(function (row) {
                    return +row[0];
                });

                labels = labels.map(function (label) {
                    return moment(label, 'YYYYMMDD').format('ddd');
                });

                let data = {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Last Week',
                            backgroundColor: "rgba(220,220,220,0.4)",
                            pointBackgroundColor: "rgba(220,220,220,1)",
                            borderColor: "rgba(220,220,220,1)",
                            borderWidth: 2,
                            data: data2
                        },
                        {
                            label: 'This Week',
                            backgroundColor: "rgba(151,187,205,0.4)",
                            pointBackgroundColor: "rgba(151,187,205,1)",
                            borderColor: "rgba(151,187,205,1)",
                            borderWidth: 2,
                            data: data1
                        }
                    ]
                };

                new Chart(makeCanvas('this-vs-last-week'), {type: 'line', data});
                generateLegend('legend-1-container', data.datasets);
            });
        }


        /**
         * Draw the a chart.js bar chart with data from the specified view that
         * overlays session data for the current year over session data for the
         * previous year, grouped by month.
         */
        function renderYearOverYearChart(ids) {
            // Adjust `now` to experiment with different days, for testing only...
            let now = moment(); // .subtract(3, 'day');

            let thisMonth = query({
                'ids': ids,
                'dimensions': 'ga:date,ga:nthDay',
                'metrics': 'ga:sessions',
                'start-date': moment(now).startOf('month').format('YYYY-MM-DD'),
                'end-date': moment(now).endOf('month').format('YYYY-MM-DD')
            });

            let lastMonth = query({
                'ids': ids,
                'dimensions': 'ga:date,ga:nthDay',
                'metrics': 'ga:sessions',
                'start-date': moment(now).subtract(1, 'month').startOf('month').format('YYYY-MM-DD'),
                'end-date': moment(now).subtract(1, 'month').endOf('month').format('YYYY-MM-DD')
            });

            Promise.all([thisMonth, lastMonth]).then(function (results: any) {

                let data1 = results[0].rows.map(function (row) {
                    return +row[2];
                });
                let data2 = results[1].rows.map(function (row) {
                    return +row[2];
                });
                let labels = new Array(31).join().split(',').map(function (item, index) {
                    return ++index;
                });

                let data = {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Last Month',
                            backgroundColor: "rgba(220,220,220,0.4)",
                            pointBackgroundColor: "rgba(220,220,220,1)",
                            borderColor: "rgba(220,220,220,1)",
                            borderWidth: 2,
                            data: data2
                        },
                        {
                            label: 'This Month',
                            backgroundColor: "rgba(151,187,205,0.4)",
                            pointBackgroundColor: "rgba(151,187,205,1)",
                            borderColor: "rgba(151,187,205,1)",
                            borderWidth: 2,
                            data: data1
                        }
                    ]
                };

                new Chart(makeCanvas('chart-2-container'), {type: 'line', data});
                generateLegend('legend-2-container', data.datasets);
            });
        }

        /**
         * Draw the a chart.js doughnut chart with data from the specified view that
         * show the top 5 browsers over the past seven days.
         */
        function renderTopBrowsersChart(ids) {
            query({
                'ids': ids,
                'dimensions': 'ga:browser',
                'metrics': 'ga:pageviews',
                'sort': '-ga:pageviews',
                'max-results': 5
            }).then(function (response: any) {
                if ( ! response.rows) response.rows = [];

                let data = {
                    labels: [],
                    datasets: [{
                        data: [],
                        backgroundColor: [],
                    }],
                };
                let colors = ['#4D5360', '#949FB1', '#D4CCC5', '#E2EAE9', '#F7464A'];

                response.rows.forEach(function (row, i) {
                    data.labels.push(row[0]);
                    data.datasets[0].data.push(+row[1]);
                    data.datasets[0].backgroundColor.push(colors[i]);
                });

                new Chart(makeCanvas('chart-3-container'), {type: 'doughnut', data, options: {legend: {position: 'bottom'}}});
            });
        }


        /**
         * Draw the a chart.js doughnut chart with data from the specified view that
         * compares sessions from mobile, desktop, and tablet over the past seven
         * days.
         */
        function renderTopCountriesChart(ids) {
            query({
                'ids': ids,
                'dimensions': 'ga:country',
                'metrics': 'ga:sessions',
                'sort': '-ga:sessions',
                'max-results': 5
            }).then(function (response: any) {
                if ( ! response.rows) response.rows = [];

                let data = {
                    labels: [],
                    datasets: [{
                        data: [],
                        backgroundColor: [],
                    }],
                };
                let colors = ['#4D5360', '#949FB1', '#D4CCC5', '#E2EAE9', '#F7464A'];

                response.rows.forEach(function (row, i) {
                    data.labels.push(row[0]);
                    data.datasets[0].data.push(+row[1]);
                    data.datasets[0].backgroundColor.push(colors[i]);
                });

                new Chart(makeCanvas('chart-4-container'), {type: 'doughnut', data, options: {legend: {position: 'bottom'}}});
            });
        }


        /**
         * Extend the Embed APIs `gapi.analytics.report.Data` component to
         * return a promise the is fulfilled with the value returned by the API.
         * @param {Object} params The request parameters.
         * @return {Promise} A promise.
         */
        function query(params) {
            return new Promise(function (resolve, reject) {
                let data = new gapi.analytics.report.Data({query: params});
                data.once('success', function (response) {
                    resolve(response);
                })
                    .once('error', function (response) {
                        reject(response);
                    })
                    .execute();
            });
        }


        /**
         * Create a new canvas inside the specified element. Set it to be the width
         * and height of its container.
         * @param {string} id The id attribute of the element to host the canvas.
         * @return {RenderingContext} The 2D canvas context.
         */
        function makeCanvas(id) {
            let container = document.getElementById(id);
            let canvas = document.createElement('canvas');
            let ctx = canvas.getContext('2d');

            if ( ! container) return;

            container.innerHTML = '';
            canvas.width = container.offsetWidth;
            canvas.height = container.offsetHeight;
            container.appendChild(canvas);

            return ctx;
        }


        /**
         * Create a visual legend inside the specified element based off of a
         * Chart.js dataset.
         * @param {string} id The id attribute of the element to host the legend.
         * @param {Array.<Object>} items A list of labels and colors for the legend.
         */
        function generateLegend(id, items) {
            let legend = document.getElementById(id);
            legend.innerHTML = items.map(function (item) {
                let color = item.color || item.backgroundColor || item.fillColor;
                let label = item.label;
                return '<li><i style="background:' + color + '"></i>' + label + '</li>';
            }).join('');
        }

        // Set some global Chart.js defaults.
        Chart.defaults.global.animationSteps = 60;
        Chart.defaults.global.animationEasing = 'easeInOutQuart';
        Chart.defaults.global.responsive = true;
        Chart.defaults.global.maintainAspectRatio = false;

        this.analyticsLoaded = true;
    }

    private renderActiveUsers() {
        gapi.analytics.ready(function () {
            gapi.analytics.createComponent("ActiveUsers", {
                initialize: function () {
                    this.activeUsers = 1;
                },
                execute: function () {
                    this.polling_ && this.stop(), this.render_(), gapi.analytics.auth.isAuthorized() ? this.getActiveUsers_() : gapi.analytics.auth.once("success", this.getActiveUsers_.bind(this))
                },
                stop: function () {
                    clearTimeout(this.timeout_), this.polling_ = !1, this.emit("stop", {
                        activeUsers: this.activeUsers
                    });
                },
                render_: function () {
                    let e = this.get();
                    this.container = "string" == typeof e.container ? document.getElementById(e.container) : e.container, this.container.innerHTML = e.template || this.template, this.container.querySelector(".count").innerHTML = this.activeUsers
                },
                getActiveUsers_: function () {
                    let e = this.get(),
                        t = 1e3 * (e.pollingInterval || 5);
                    if (isNaN(t) || 5e3 > t) throw new Error("Frequency must be 5 seconds or more.");
                    this.polling_ = !0, gapi.client.analytics.data.realtime.get({
                        ids: e.ids,
                        metrics: "rt:activeUsers"
                    }).execute(function (e) {
                        let i = e.totalResults ? +e.rows[0][0] : 1,
                            s = this.activeUsers;
                        this.emit("success", {
                            activeUsers: this.activeUsers
                        }), i != s && (this.activeUsers = i, this.onChange_(i - s)), (this.polling_ = !0) && (this.timeout_ = setTimeout(this.getActiveUsers_.bind(this), t))
                    }.bind(this))
                },
                onChange_: function (e) {
                    let t = this.container.querySelector(".count");
                    t && (t.innerHTML = this.activeUsers), this.emit("change", {
                        activeUsers: this.activeUsers,
                        delta: e
                    }), e > 0 ? this.emit("increase", {
                        activeUsers: this.activeUsers,
                        delta: e
                    }) : this.emit("decrease", {
                        activeUsers: this.activeUsers,
                        delta: e
                    })
                },
                template: '<div class="count ActiveUsers-value"></div><div class="name">Users Online</div>'
            })
        });
    }
}

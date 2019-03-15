import {KarmaTest} from "../../../../../testing/karma-test";
import {PaginationControlsComponent} from "./pagination-controls.component";
import {UrlAwarePaginator} from "../url-aware-paginator.service";

describe('PaginationControlsComponent', () => {

    let testBed: KarmaTest<any>;

    beforeEach(() => {
        testBed = new KarmaTest({
            module: {
                declarations: [PaginationControlsComponent],
                providers: [],
            },
            component: PaginationControlsComponent,
        });

        testBed.component.paginator = new UrlAwarePaginator(testBed.fixture.debugElement.injector);
        testBed.component.paginator.data = [];
    });

    it('renders pagination meta information', () => {
        testBed.component.paginator.params.to = 1;
        testBed.component.paginator.params.from = 2;
        testBed.component.paginator.params.total = 3;
        testBed.component.paginator.params.currentPage = 2;
        testBed.component.itemsName = 'foo bar';

        testBed.fixture.detectChanges();

        expect(testBed.find('.pagination-to').textContent).toEqual('1');
        expect(testBed.find('.pagination-from').textContent).toEqual('2');
        expect(testBed.find('.pagination-total').textContent).toEqual('3');
        expect(testBed.find('.items-name').textContent).toEqual('foo bar');
    });

    it('changes paginator "perPage" setting', () => {
        spyOn(testBed.component.paginator, 'onParamChange');
        testBed.component.paginator.params.currentPage = 2;
        testBed.fixture.detectChanges();

        testBed.find('#per-page')['value'] = 50;
        testBed.dispatchEvent('#per-page', 'change');

        //model is bound properly
        expect(testBed.component.paginator.params.perPage).toEqual('50');

        //calls "onParamChange" on paginator
        expect(testBed.component.paginator.onParamChange).toHaveBeenCalledWith('perPage');
    });

    it('renders pagination controls', () => {
        testBed.component.paginator.params.perPage = testBed.component.defaultPerPage + 1;
        spyOn(testBed.component.paginator, 'firstPage'); spyOn(testBed.component.paginator, 'prevPage');
        spyOn(testBed.component.paginator, 'nextPage'); spyOn(testBed.component.paginator, 'lastPage');
        spyOn(testBed.component.paginator, 'isFirstPage').and.returnValue(true);
        spyOn(testBed.component.paginator, 'hasPrev').and.returnValue(false);
        spyOn(testBed.component.paginator, 'hasNext').and.returnValue(false);
        spyOn(testBed.component.paginator, 'isLastPage').and.returnValue(true);
        testBed.fixture.detectChanges();

        //first page button, adds "disabled" class and navigates to first page
        expect(testBed.find('.first-page-button').classList.contains('disabled')).toEqual(true);
        testBed.find('.first-page-button').click();
        expect(testBed.component.paginator.firstPage).toHaveBeenCalledTimes(1);

        //previous page button, adds "disabled" class and navigates to previous page
        expect(testBed.find('.previous-page-button').classList.contains('disabled')).toEqual(true);
        testBed.find('.previous-page-button').click();
        expect(testBed.component.paginator.prevPage).toHaveBeenCalledTimes(1);

        //next page button, adds "disabled" class and navigates to next page
        expect(testBed.find('.next-page-button').classList.contains('disabled')).toEqual(true);
        testBed.find('.next-page-button').click();
        expect(testBed.component.paginator.nextPage).toHaveBeenCalledTimes(1);

        //last page button, adds "disabled" class and navigates to last page
        expect(testBed.find('.last-page-button').classList.contains('disabled')).toEqual(true);
        testBed.find('.last-page-button').click();
        expect(testBed.component.paginator.lastPage).toHaveBeenCalledTimes(1);
    });

    it('shows controls only if they are needed', () => {
        //shows controls if there are enough items to paginate
        testBed.component.paginator.params.total = 10;
        testBed.component.paginator.params.perPage = 5;
        testBed.fixture.detectChanges();
        expect(testBed.find('.pagination-controls-inner')).toBeTruthy();

        //hides controls if there are not enough items to paginate
        testBed.component.paginator.params.total = testBed.component.defaultPerPage - 1;
        testBed.component.paginator.params.perPage = testBed.component.defaultPerPage;
        testBed.fixture.detectChanges();
        expect(testBed.find('.pagination-controls-inner')).toBeFalsy();

        //shows controls if current page is not 1, even if there are not enough items
        testBed.component.paginator.params.currentPage = 2;
        testBed.fixture.detectChanges();
        expect(testBed.find('.pagination-controls-inner')).toBeTruthy();

        //hides controls if current page is 1 and there's not enough items
        testBed.component.paginator.params.currentPage = 1;
        testBed.fixture.detectChanges();
        expect(testBed.find('.pagination-controls-inner')).toBeFalsy();

        //shows controls if "per_page" is changed from default value
        testBed.component.paginator.params.currentPage = 1;
        testBed.component.paginator.params.perPage = testBed.component.defaultPerPage + 1;
        testBed.fixture.detectChanges();
        expect(testBed.find('.pagination-controls-inner')).toBeTruthy();

        //shows controls if "per_page" is default and there's not enough items
        testBed.component.paginator.params.currentPage = 1;
        testBed.component.paginator.params.perPage = testBed.component.defaultPerPage;
        testBed.fixture.detectChanges();
        expect(testBed.find('.pagination-controls-inner')).toBeFalsy();

        //shows controls if "alwaysShow" is set to true
        testBed.component.paginator.params.currentPage = 1;
        testBed.component.paginator.params.total = testBed.component.defaultPerPage - 1;
        testBed.component.paginator.params.perPage = testBed.component.defaultPerPage;
        testBed.component.alwaysShow = true;
        testBed.fixture.detectChanges();
        expect(testBed.find('.pagination-controls-inner')).toBeTruthy();

        //hides controls if there's no paginator
        testBed.component.paginator = null;
        testBed.fixture.detectChanges();
        expect(testBed.find('.pagination-controls-inner')).toBeFalsy();
    });
});
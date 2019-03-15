<html>
    <head>
        <title>{{ $settings->get('branding.site_name') }}</title>

        <base href="{{ $htmlBaseUri }}">

        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <link href='https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,400italic' rel='stylesheet' type='text/css'>
        <link rel="icon" type="image/x-icon" href="{{$settings->get('branding.favicon')}}">

        {{--angular styles begin--}}
		<link href="styles.a4f155c37c84c8d6c5d6.bundle.css" rel="stylesheet">
		{{--angular styles end--}}

        {{--custom theme begin--}}
        @if ($settings->get('branding.use_custom_theme'))
            <link rel="stylesheet" href="{{asset('storage/appearance/theme.css')}}">
        @endif
        {{--custom theme end--}}

        @if ($settings->has('custom_code.css'))
            <style>{!! $settings->get('custom_code.css') !!}</style>
        @endif
	</head>

    <body>
        <app-root></app-root>

        <script>
            window.bootstrapData = "{!! $bootstrapData !!}";
        </script>

        {{--angular scripts begin--}}
		<script type="text/javascript" src="inline.d0fb224123eb8c64aab7.bundle.js"></script>
		<script type="text/javascript" src="polyfills.1c1eed1c2ffd549254d2.bundle.js"></script>
		<script type="text/javascript" src="main.2eef3f147534abe6717d.bundle.js"></script>
		{{--angular scripts end--}}

        @if ($settings->has('custom_code.js'))
            <script>{!! $settings->get('custom_code.js') !!}</script>
        @endif

        @if ($code = $settings->get('analytics.tracking_code'))
            <script>
                (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
                })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

                ga('create', '{{ $settings->get('analytics.tracking_code') }}', 'auto');
                ga('send', 'pageview');
            </script>
        @endif

        <script>var adParams = {s: 1000340453, w: 300, h: 250, c: 4, blank: false, title: "COLOCA AQUI EL NOMBRE DE ARCHIVO / VIDEO DINAMICAMENTE OPCIONALMENTE" };</script>
	</body>
</html>
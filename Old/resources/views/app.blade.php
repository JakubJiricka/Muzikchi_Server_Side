@extends('vebto::framework')
@section('angular-styles')
    {{--angular styles begin--}}
		<link rel="stylesheet" href="client/styles.d9d788ff5d3f58ff4316.css">
	{{--angular styles end--}}
@endsection

@section('angular-scripts')
    {{--angular scripts begin--}}
		<script>
        if (window.location.href.indexOf('admin') === -1) {
            document.body.style.background = '#1e1e26';
        }
    </script>
		<script>setTimeout(function() {
        var spinner = document.querySelector('.la-line-scale');
        if (spinner) spinner.style.display = 'block';
    }, 100);</script>
		<script type="text/javascript" src="client/runtime.94d23f66172e5507691c.js"></script>
		<script type="text/javascript" src="client/polyfills.5983baea43d7e1abb4a3.js"></script>
		<script type="text/javascript" src="client/main.8b1cf8b79af6550dab10.js"></script>
	{{--angular scripts end--}}
@endsection

@section('before-loaded-content')
	<style>.global-spinner{display: flex; align-items: center; justify-content: center; z-index: 999; position: fixed; top: 0; left: 0; width: 100%; height: 100%;}</style>
	<style>.la-line-scale,.la-line-scale>div{position:relative;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box}.la-line-scale{display:none;font-size:0;color:#6eac00}.la-line-scale.la-dark{color:#333}.la-line-scale>div{display:inline-block;float:none;background-color:currentColor;border:0 solid currentColor}.la-line-scale{width:80px;height:70px}.la-line-scale>div{width:6px;height:100%;margin:0
		2px;border-radius:0;-webkit-animation:line-scale 1.2s infinite ease;-moz-animation:line-scale 1.2s infinite ease;-o-animation:line-scale 1.2s infinite ease;animation:line-scale 1.2s infinite ease}.la-line-scale>div:nth-child(1){-webkit-animation-delay:-1.2s;-moz-animation-delay:-1.2s;-o-animation-delay:-1.2s;animation-delay:-1.2s}.la-line-scale>div:nth-child(2){-webkit-animation-delay:-1.1s;-moz-animation-delay:-1.1s;-o-animation-delay:-1.1s;animation-delay:-1.1s}.la-line-scale>div:nth-child(3){-webkit-animation-delay:-1s;-moz-animation-delay:-1s;-o-animation-delay:-1s;animation-delay:-1s}.la-line-scale>div:nth-child(4){-webkit-animation-delay:-.9s;-moz-animation-delay:-.9s;-o-animation-delay:-.9s;animation-delay:-.9s}.la-line-scale>div:nth-child(5){-webkit-animation-delay:-.8s;-moz-animation-delay:-.8s;-o-animation-delay:-.8s;animation-delay:-.8s}.la-line-scale.la-sm{width:20px;height:16px}@-webkit-keyframes line-scale{0%,40%,100%{-webkit-transform:scaleY(0.4);transform:scaleY(0.4)}20%{-webkit-transform:scaleY(1);transform:scaleY(1)}}@-moz-keyframes line-scale{0%,40%,100%{-webkit-transform:scaleY(0.4);-moz-transform:scaleY(0.4);transform:scaleY(0.4)}20%{-webkit-transform:scaleY(1);-moz-transform:scaleY(1);transform:scaleY(1)}}@-o-keyframes line-scale{0%,40%,100%{-webkit-transform:scaleY(0.4);-o-transform:scaleY(0.4);transform:scaleY(0.4)}20%{-webkit-transform:scaleY(1);-o-transform:scaleY(1);transform:scaleY(1)}}@keyframes line-scale{0%,40%,100%{-webkit-transform:scaleY(0.4);-moz-transform:scaleY(0.4);-o-transform:scaleY(0.4);transform:scaleY(0.4)}20%{-webkit-transform:scaleY(1);-moz-transform:scaleY(1);-o-transform:scaleY(1);transform:scaleY(1)}}
	</style>
	<div class="global-spinner">
		<div class="la-line-scale">
			<div></div>
			<div></div>
			<div></div>
			<div></div>
			<div></div>
		</div>
	</div>
	<script>setTimeout(function() {
            var spinner = document.querySelector('.la-line-scale');
            if (spinner) spinner.style.display = 'block';
        }, 100);</script>
@endsection
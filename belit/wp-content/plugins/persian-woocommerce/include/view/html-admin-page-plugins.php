<?php
/**
 * Admin View: Gateways
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

for ( $i = 0; $i < 3; $i ++ ) {
	$response = wp_remote_get( 'http://woocommerce.ir/api/gateways.json' );
	if ( empty( $response->errors ) ) {
		break;
	}
}

if ( ! empty( $response->errors ) ) {
	echo '<br><h1>در اتصال به سرور خطایی رخ داده است. لطفا چند دقیقه دیگر تلاش کنید.</h1>';

	return;
}

if ( is_array( $response ) ) {
	$response = json_decode( $response['body'] );
}

$count = 0;
?>
<div class="wrap woocommerce wc_addons_wrap">
    <h1><?php echo get_admin_page_title(); ?></h1>


    <div class="addons-featured">
        <div class="addons-banner-block">
            <h1>افزونه های ووکامرس فارسی</h1>
            <h3>ارائه افزونه های معتبر و فارسی با امنیت بالا و پشتیبانی 24 ساعته</h3>
            <p>ارائه برترین افزونه ها با پشتیبانی دائمی و امنیت بالا برای ووکامرس فارسی ، پیاده سازی و توسعه توسط
                متخصصین ووکامرس در ایران. افزونه های معتبر و اصلی تنها در ووکامرس فارسی عرضه می گردند. برای اطلاع از
                آخرین تخفیف ها و اطلاعیه های ووکامرس ، <a target="_blank" href="https://t.me/woocommerce_ir">کانال
                    تلگرام ووکامرس فارسی</a> را دنبال کنید</p>

			<?php foreach ( $response as $item ) : ?>
				<?php if ( $count ++ % 3 == 0 ) {
					echo '<div class="addons-banner-block-items">';
				} ?>
                <div class="addons-banner-block-item">
                    <div class="addons-banner-block-item-icon"
                         style="background: linear-gradient(rgba(255,255,255,0.9),rgba(255,255,255,0.9)),url('<?php echo esc_url( $item->image ); ?>')!important;">
                        <img class="addons-img" src="<?php echo esc_url( $item->image ); ?>">

                    </div>
                    <div class="addons-banner-block-item-content">
                        <h3><?php echo esc_html( $item->title ); ?></h3>

                        <p><?php

							$string = strip_tags( $item->desc );

							if ( strlen( $string ) > 220 ) {
								$stringCut = substr( $string, 0, 220 );
								$string    = substr( $stringCut, 0, strrpos( $stringCut, ' ' ) ) . '... <a href="' . esc_url( $item->url ) . '">اطلاعات بیشتر</a>';
							}
							echo $string; ?></p>
                        <a class="addons-button addons-button-solid"
                           href="<?php echo esc_url( $item->url ); ?>"><?php echo esc_html( $item->price ); ?> / اطلاعات
                            بیشتر</a>
                    </div>
                </div>
				<?php if ( $count % 3 == 0 ) {
					echo '</div>';
				} ?>
			<?php endforeach; ?>

        </div>
    </div>
</div>


<style type="text/css">
    .ver {
        -webkit-transform: rotate(-45deg);
        -moz-transform: rotate(-45deg);
        -o-transform: rotate(-45deg);
        -ms-transform: rotate(-45deg);
        transform: rotate(-45deg);
        position: relative;
        top: 0px;
        left: -40px;
    }

    img.addons-img {
        height: 100% !important;
        opacity: 1.0;
        border-right: 1px solid #e4e4e4;
        border-left: 1px solid #e4e4e4;
    }

    .addons-banner-block-item-icon {
        height: 200px;
    }

    .addons-button {
        width: auto !important;
    }

    .addons-banner-block-item-content p {
        text-align: justify;
    }
</style>

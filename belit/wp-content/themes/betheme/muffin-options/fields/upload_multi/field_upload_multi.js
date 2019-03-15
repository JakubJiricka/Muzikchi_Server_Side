/**
 * MfnMultiupload - closure thats describe behaviour of Image Gallery
 *
 * @param  {Object} $     - jQuery reference
 * @return {Object} .init - Method to start the closure
 */
var Mfn_Upload_Multi = ( function( $ ){

	
	/**
	 * Global variables
	 */
	var multi_file_frame		= null,
		multi_file_frame_open	= null,
		multi_file_frame_select	= null;

	var selector = '.mfnf-upload.multi';
	

	/**
	 * Attach events to buttons. Runs whole script.
	 */
	function init() {
		
		open_media_gallery();
		attach_remove_action();
		attach_remove_all_action();
		
		ui_sortable();
		
	}
	
	
	/**
	 * UI Sortable Init
	 */
	function ui_sortable(){

		$( '.gallery-container', selector ).hover( function(){
			
			var el = $( this ),
				parent = el.closest( selector );

			if( $( '.image-container', el ).length ){
				
				// init sortable
				if( ! el.hasClass( 'ui-sortable' ) ){
					el.sortable({
						opacity	: 0.9,
						update	: function(){
							fill_input( parent, find_all_ids( parent ) );
						}
					});
				}
				
				// enable inactive sortable
				if( el.hasClass( 'ui-sortable-disabled' ) ){
					el.sortable( 'enable' );
				}
			}

		});
	}
	

	/**
	 * Click | Add
	 */
	function open_media_gallery(){
		$( '.upload-add', selector ).click( function( event ) {
	    	event.preventDefault();

	        // Create the media frame
	        multi_file_frame = wp.media.frames.mfnGallery = wp.media({
	        	title   	: $( this ).data( 'button' ),
				multiple	: 'add',
	        	library 	: {
					type : 'image',
	        	},
	            button		: {
	                text : $( this ).data( 'button' )
	            }
	        });     

			// Attach hooks to the events
	        
	        multi_file_frame.on( 'open'		, multi_file_frame_open   );
	        multi_file_frame.on( 'select'	, multi_file_frame_select );

			multi_file_frame.open();
	    });
	}

	
	/**
	 * WP Media Frame | Open
	 */
	 multi_file_frame_open = function(){
		 
		var handle 		= $( multi_file_frame.modal.clickedOpenerEl ),
			parent 		= handle.closest( selector ),
			library		= multi_file_frame.state().get( 'selection' ),
			images 		= $( '.upload-input', parent ).val(),
			image_ids;

		if( ! images ){
			return true;
		}

		image_ids = images.split( ',' );

		image_ids.forEach( function( id ){
			var attachment = wp.media.attachment( id );
			attachment.fetch();
			library.add( attachment ? [ attachment ] : [] );
		});
	};


	/**
	 * WP Media Frame | Select
	 */
	multi_file_frame_select = function () {
		
		var handle 		= $( multi_file_frame.modal.clickedOpenerEl ),
		 	parent 		= handle.closest( selector ),
		 	gallery 	= $( '.gallery-container', parent ),
		 	library		= multi_file_frame.state().get( 'selection' ),
	    	image_urls	= [],
	    	image_ids	= [],
	    	image_url, output_html, joined_ids;

	    	gallery.html( '' );

	        library.map( function( image ){

	    		image = image.toJSON();
	     		image_urls.push( image.url );
	      		image_ids.push( image.id );
	      		
	      		if( image.sizes.thumbnail ){
	      			image_url = image.sizes.thumbnail.url;
	      		} else {
	      			image_url = image.url;
	      		}

	      		output_html  = 	'<div class="image-container">' +
									'<img class="screenshot image" src="'+ image_url +'" data-pic-id="'+ image.id +'" />' +
									'<a href="#" class="upload-remove single dashicons dashicons-no"></a>' +
								'</div>';

				gallery.append( output_html );
	        });

		joined_ids = image_ids.join( ',' ).replace(/^,*/, '' );
		if ( joined_ids.length !== 0 ) {
			$( 'a.upload-remove.all', parent ).fadeIn( 300 );
		}
		
		fill_input( parent, joined_ids );
		
		attach_remove_action();
	};
	
	
	/**
	 * Click | Remove single
	 */
	function attach_remove_action() {
	    $( 'a.upload-remove.single', selector ).click( function( event ){
	    	event.preventDefault();

	        var handle = $( this ),
				parent = handle.closest( selector ),
				joined_ids;
			 
			handle.closest( '.image-container' ).remove();

			joined_ids = find_all_ids( parent );
			if( joined_ids === '' ) {
				$( 'a.upload-remove.all', parent ).fadeOut( 300 );
			}

			fill_input( parent, joined_ids );
	    });
	}


	/**
	 * Click | Remove all
	 */
	function attach_remove_all_action() {
	    $( '.upload-remove.all', selector ).click( function( event ) {
	    	event.preventDefault();
	    	
	    	var handle = $( this ),
	    		parent = handle.closest( selector );

	        $( this ).fadeOut( 300 );
	        
	        $( 'input', parent ).val( '' );
	        $( '.gallery-container', parent ).html( '' );

	    });
	}


	/**
	 * Helper method. Find all IDs of added images.
	 * @method find_all_ids
	 * @return {String}		joined ids separated by `;`
	 */
	function find_all_ids( parent ) {
		var image_ids = [],
			id;

		$( '.gallery-container img.screenshot', parent ).each( function(){
			id =  $( this ).attr( 'data-pic-id' );
			image_ids.push( id );
		});

		return image_ids.join( "," );
	}


	/**
	 * Helper method. Set the value of image_gallery input.
	 * @method fill_input
	 * @param  {String} joined_ids - string to be set into input
	 */
	function fill_input( parent, joined_ids ){
		
        $( '.upload-input', parent )
    		.val( joined_ids )
    		.trigger( 'change' );
	}

	
	/**
	 * Return
	 * Method to start the closure
	 */
	return {
		init: init
	};

})( jQuery );

jQuery( document ).ready( function(){
	var mfn_upload_multi = Mfn_Upload_Multi.init();
});

;( function ( $, window, undefined ) {
	"use strict";

	$( document ).ready( function () {
		if ( typeof labVcMaps == 'undefined' ) {
			return;
		}

		$.each( labVcMaps, function ( mapIndex, mapEntry ) {

			google.maps.event.addDomListener( window, 'load', function () {
				var bounds = new google.maps.LatLngBounds(),
					markers = [],
					zoom = mapEntry.zoom,
					options = {
						center : {
							lat : -34.397,
							lng : 150.644
						},

						scrollwheel : mapEntry.scrollwheel,
						draggable : $( window ).width() > 768 ? true : mapEntry.scrollwheel,
						panControl : mapEntry.panControl,
						zoomControl : mapEntry.zoomControl,
						mapTypeControl : mapEntry.mapTypeControl,
						scaleControl : mapEntry.scaleControl,
						streetViewControl : mapEntry.streetViewControl,
						overviewMapControl : mapEntry.overviewMapControl,
						fullscreenControl : mapEntry.fullscreenControl,

						mapTypeId : mapEntry.mapType
					},
					map = new google.maps.Map( document.getElementById( mapEntry.id ), options );

				mapEntry.mapInstance = map;
				mapEntry.mapMarkers = [];

				jQuery.each( mapEntry.locations, function ( i, location ) {
					var latlng = new google.maps.LatLng( location.latitude, location.longitude ),
						delay = ( i + 1 ) * 150 * ( mapEntry.dropPins ? 1 : 0 );

					bounds.extend( latlng );

					delay = Math.min( 1000, delay );

					setTimeout( function () {

						// Marker image
						var markerImage = {};

						if ( location.marker_image ) {
							$.extend( markerImage, {
								url : location.marker_image,
							} );

							if ( location.hasOwnProperty( 'marker_image_size' ) ) {
								var size = location.marker_image_size,
									width = size[ 0 ],
									height = size[ 1 ];

								if ( 'yes' == location.retina_marker ) {
									width = width / 2;
									height = height / 2;
								}

								$.extend( markerImage, {
									scaledSize : new google.maps.Size( width, height ),
								} );
							}
						}

						var marker_id = markers.push( new google.maps.Marker( {
							position : latlng,
							map : map,
							title : latlng.marker_title,
							icon : markerImage,
							optimized : false,
							animation : mapEntry.dropPins ? google.maps.Animation.DROP : null
						} ) );

						var marker = markers[ marker_id - 1 ];

						var markerEntry = {
							marker : marker,
							infowindow : null
						}

						if ( location.marker_title || location.marker_description ) {
							var content_string = '<style>p { margin: 0; } p + p { margin-top: 10px; }</style>';

							if ( location.marker_title ) {
								content_string += '<h3 style="margin: 0; font-size: 18px;">' + location.marker_title + '</h3>';
							}

							if ( location.marker_description ) {
								content_string += '<div style="margin-top: 10px;">' + location.marker_description + '</div>';
							}

							var infowindow = new google.maps.InfoWindow( {
								content : content_string
							} );

							google.maps.event.addListener( marker, 'click', function () {

								$.each( mapEntry.mapMarkers, function ( i, _marker ) {
									if ( _marker.infowindow ) {
										_marker.infowindow.close();
									}
								} );

								infowindow.open( map, marker );
							} );

							markerEntry.infowindow = infowindow;
						}

						mapEntry.mapMarkers.push( markerEntry );

					}, delay );
				} );


				if ( mapEntry.zoom > 0 ) {
					map.setCenter( bounds.getCenter() );
					map.setZoom( mapEntry.zoom );
				} else {
					map.fitBounds( bounds );
				}

				if ( mapEntry.styles ) {
					map.setOptions( {
						styles : mapEntry.styles
					} );
				}

				if ( mapEntry.tilt ) {
					map.setTilt( mapEntry.tilt );
				}

				if ( mapEntry.heading ) {
					map.setHeading( mapEntry.heading );
				}

				map.panBy( parseInt( mapEntry.panBy[ 0 ], 10 ), parseInt( mapEntry.panBy[ 1 ], 10 ) );

				if ( mapEntry.plusMinusZoom ) {
					var customZoomControl = function ( controlDiv, map ) {
						var $zooms = $( "#" + mapEntry.id ).parent().find( '.cd-zoom' );

						$zooms.removeClass( 'hidden' );

						var controlUIzoomIn = $zooms.filter( '.cd-zoom-in' )[ 0 ],
							controlUIzoomOut = $zooms.filter( '.cd-zoom-out' )[ 0 ];

						controlDiv.appendChild( controlUIzoomIn );
						controlDiv.appendChild( controlUIzoomOut );

						google.maps.event.addDomListener( controlUIzoomIn, 'click', function () {
							map.setZoom( map.getZoom() + 1 );
						} );

						google.maps.event.addDomListener( controlUIzoomOut, 'click', function () {
							map.setZoom( map.getZoom() - 1 );
						} );
					}

					var zoomControlDiv = document.createElement( 'div' );
					zoomControlDiv.setAttribute( 'className', 'cd-zoom-controllers' );
					var zoomControl = new customZoomControl( zoomControlDiv, map );

					map.controls[ google.maps.ControlPosition.LEFT_TOP ].push( zoomControlDiv );
				}

			} );
		} );
	} );

} )( jQuery, window );
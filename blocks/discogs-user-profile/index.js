var { useState, useEffect } = wp.element;
var apiFetch = wp.apiFetch;
var AlignmentToolbar = wp.blockEditor.AlignmentToolbar;
var BlockControls = wp.blockEditor.BlockControls;
var BorderControl = wp.components.BorderControl;
var BoxControl = wp.components.BoxControl;
var Button = wp.components.Button;
var ButtonGroup = wp.components.ButtonGroup;
var ColorPalette = wp.blockEditor.ColorPalette || wp.components.ColorPalette;
var ColorPicker = wp.components.ColorPicker;
var createElement = wp.element.createElement;
var Fragment = wp.element.Fragment;
var FontSizePicker  = wp.components.FontSizePicker;
var InnerBlocks = wp.blockEditor.InnerBlocks;
var InspectorControls = wp.blockEditor.InspectorControls;
var MediaUpload = wp.blockEditor.MediaUpload;
var MediaUploadCheck = wp.blockEditor.MediaUploadCheck;
var PanelBody = wp.components.PanelBody;
var registerBlockType = wp.blocks.registerBlockType;
var RangeControl = wp.components.RangeControl;
var RichText = wp.blockEditor.RichText;
var SelectControl = wp.components.SelectControl;
var ServerSideRender  = wp.serverSideRender;
var TextareaControl = wp.components.TextareaControl;
var TextControl = wp.components.TextControl;
var ToggleControl = wp.components.ToggleControl;
var Toolbar = wp.components.Toolbar;
var useBlockProps = wp.blockEditor.useBlockProps;
var useSelect = wp.data.useSelect;

var customIcon__Discogs = createElement(
	'svg',
	{ xmlns: 'http://www.w3.org/2000/svg', viewBox: '0 0 24 24' },
	createElement('path', {
		d: 'M12,2.9C12,2.9,11.9,2.9,12,2.9C11.9,2.9,12,2.9,12,2.9z M8.7,12c0-1.9,1.5-3.4,3.4-3.4c0.6,0,1.2,0.2,1.7,0.5l0.7-1.2 	c-0.7-0.4-1.5-0.6-2.3-0.6c-1.3,0-2.5,0.5-3.3,1.4c-0.9,0.9-1.4,2-1.4,3.3s0.5,2.5,1.4,3.3c0,0,0.1,0.1,0.1,0.1l0.9-1c0,0,0,0,0,0 	C9.1,13.8,8.7,12.9,8.7,12z M15.4,8.7l-0.9,1c0.6,0.6,0.9,1.4,0.9,2.3c0,1.9-1.5,3.4-3.3,3.4c-0.6,0-1.1-0.1-1.6-0.4l0,0l-0.7,1.2 	c0.7,0.4,1.4,0.6,2.2,0.6c1.3,0,2.5-0.5,3.3-1.4c0.9-0.9,1.4-2,1.4-3.3C16.8,10.7,16.2,9.5,15.4,8.7z M9.4,12c0-1.4,1.2-2.6,2.6-2.6 	l0,0c1.4,0,2.6,1.2,2.6,2.6l0,0c0,1.4-1.2,2.6-2.6,2.6l0,0C10.6,14.6,9.4,13.4,9.4,12L9.4,12z M9.6,12c0,1.4,1.1,2.5,2.5,2.5l0,0 	c1.4,0,2.5-1.1,2.5-2.5l0,0c0-1.4-1.1-2.5-2.5-2.5l0,0C10.7,9.5,9.6,10.6,9.6,12L9.6,12z M12.4,12c0,0.2-0.2,0.4-0.4,0.4 	c-0.2,0-0.4-0.2-0.4-0.4s0.2-0.4,0.4-0.4C12.3,11.6,12.4,11.8,12.4,12z M7.7,16.5l1-1c-0.9-0.9-1.5-2.1-1.5-3.5 	c0-2.7,2.1-4.8,4.8-4.8c0.9,0,1.7,0.2,2.4,0.6l0.7-1.2c-0.9-0.5-1.9-0.8-3-0.8c-1.7,0-3.3,0.7-4.4,1.8S5.8,10.3,5.8,12 	s0.7,3.3,1.8,4.4C7.7,16.4,7.7,16.5,7.7,16.5z M16.4,7.6C16.4,7.6,16.4,7.6,16.4,7.6l-1,1c0.9,0.9,1.4,2.1,1.4,3.4 	c0,2.6-2.1,4.8-4.8,4.8c-0.8,0-1.6-0.2-2.3-0.6l-0.7,1.2c0.9,0.5,1.9,0.8,3,0.8c1.7,0,3.3-0.7,4.4-1.8c1.1-1.1,1.8-2.7,1.8-4.4 	C18.2,10.3,17.5,8.7,16.4,7.6z M17.5,6.6C17.4,6.5,17.4,6.5,17.5,6.6l-1,1c1.1,1.1,1.9,2.7,1.9,4.5c0,3.5-2.8,6.3-6.3,6.3 	c-1.1,0-2.1-0.3-3-0.8l-0.7,1.2c1.1,0.6,2.3,0.9,3.7,0.9c2.1,0,4-0.9,5.4-2.3c1.4-1.4,2.2-3.3,2.2-5.4C19.7,9.9,18.8,7.9,17.5,6.6z 	 M6.7,17.6l1-1c-1.2-1.1-2-2.8-2-4.6c0-3.5,2.8-6.3,6.3-6.3c1.1,0,2.2,0.3,3.1,0.8l0.7-1.2c-1.1-0.6-2.4-1-3.8-1 	c-2.1,0-4,0.9-5.4,2.3C5.2,7.9,4.3,9.9,4.3,12s0.9,4,2.3,5.4C6.6,17.5,6.7,17.5,6.7,17.6z M5.8,18.6 M7.6,20 M18.3,5.5l-0.9,0.9 	c1.4,1.4,2.3,3.4,2.3,5.5c0,4.3-3.5,7.8-7.8,7.8c-1.4,0-2.6-0.4-3.7-1L7.6,20c1.3,0.7,2.8,1.1,4.4,1.1c5,0,9.1-4.1,9.1-9.1 	C21.1,9.5,20,7.2,18.3,5.5z M12.2,2.9H12c-5,0-9,4-9.1,9l0,0V12c0,2.6,1.1,4.9,2.9,6.6l0.9-1c-1.5-1.4-2.4-3.4-2.4-5.6 	c0-4.3,3.5-7.8,7.8-7.8c1.4,0,2.7,0.4,3.8,1l0.6-1.1C15.2,3.4,13.7,3,12.2,2.9z'
	})
);

var nameDisplay = [
	{ label: 'Username', value: 'username' },
	{ label: 'Name', value: 'name' }
];

registerBlockType('blocks-for-discogs/discogs-user-profile', {
 	icon: {
		background: '#333333', 
		foreground: '#ffffff',
		src: customIcon__Discogs
	},
    edit: function (props) {

        var attributes = props.attributes;
        var setAttributes = props.setAttributes;

		if (!attributes.blockID) {
			setAttributes({ blockID: 'wp-block-'+Math.random().toString(36).slice(2, 12) });
		}
		
		var InspectorPanel = createElement(InspectorControls, null,
			createElement(PanelBody, null,

				createElement(TextControl, {
					label: "Username",
					value: attributes.username,
					onChange: function (value) {
						return setAttributes({ username: value });
					}
				}),
				createElement('label', {className:'discogs-label'},'USERNAME DISPLAY'),

				createElement(ButtonGroup, {style:{marginBottom:'16px'}},
					nameDisplay.map( function( option ) {
						return createElement(Button,{
							isPressed: attributes.display_profile_name_or_username === option.value,
							onClick: function() {
								setAttributes({ display_profile_name_or_username: option.value });
							},
							key: option.value
						},option.label);
					})
				),

				createElement('label', {className:'discogs-label'},'COLLECTION VALUE'),
				createElement('small', {style:{lineHeight:1.1,marginBottom:'10px',display:'block'}},'This setting only works for usernames that match the personal access token saved in settings.'),

				createElement(ToggleControl, {
					label: 'Display Collection Value?', 
					checked: attributes.display_profile_collection_value, 
					onChange: function(value) { setAttributes({ display_profile_collection_value: value }); } 
				}),

				createElement(BorderControl, {
					label: 'Border',
					value: attributes.profile_wrap_border,
					onChange: function (newValues) {
						setAttributes({ profile_wrap_border: newValues });
					},
					enable: ['width', 'style', 'color', 'radius'],
					colors: wp.data.select('core/block-editor').getSettings().colors
				}),	

				createElement('br'),
				createElement('label', {className:'discogs-label',style:{fontWeight:'bold'}},'DISPLAY OPTIONS'),
				createElement('small', '','Choose which elements to display or hide'),
				createElement('div',{style:{marginBottom:'10px'}},''),

				createElement(ToggleControl, {
					label: 'Banner', 
					checked: attributes.display_profile_banner, 
					onChange: function(value) { setAttributes({ display_profile_banner: value }); } 
				}),
				createElement(ToggleControl, {
					label: 'Avatar', 
					checked: attributes.display_profile_avatar, 
					onChange: function(value) { setAttributes({ display_profile_avatar: value }); } 
				}),	
				createElement(ToggleControl, {
					label: 'Title (username/name)', 
					checked: attributes.display_profile_title, 
					onChange: function(value) { setAttributes({ display_profile_title: value }); } 
				}),
				createElement(ToggleControl, {
					label: 'Registration Date', 
					checked: attributes.display_profile_registration_date, 
					onChange: function(value) { setAttributes({ display_profile_registration_date: value }); } 
				}),
				createElement(ToggleControl, {
					label: 'Location', 
					checked: attributes.display_profile_location, 
					onChange: function(value) { setAttributes({ display_profile_location: value }); } 
				}),
				createElement(ToggleControl, {
					label: 'Number in Collection', 
					checked: attributes.display_profile_num_collection, 
					onChange: function(value) { setAttributes({ display_profile_num_collection: value }); } 
				}),
				createElement(ToggleControl, {
					label: 'Number in Wantlist', 
					checked: attributes.display_profile_num_wantlist, 
					onChange: function(value) { setAttributes({ display_profile_num_wantlist: value }); } 
				}),
				createElement(ToggleControl, {
					label: 'Number for Sale', 
					checked: attributes.display_profile_num_for_sale, 
					onChange: function(value) { setAttributes({ display_profile_num_for_sale: value }); } 
				}),
				createElement(ToggleControl, {
					label: 'Contributed Count', 
					checked: attributes.display_profile_releases_contributed, 
					onChange: function(value) { setAttributes({ display_profile_releases_contributed: value }); } 
				}),
				createElement(ToggleControl, {
					label: 'Rated Count', 
					checked: attributes.display_profile_releases_rated, 
					onChange: function(value) { setAttributes({ display_profile_releases_rated: value }); } 
				}),
				createElement(ToggleControl, {
					label: 'Profile Text', 
					checked: attributes.display_profile_text, 
					onChange: function(value) { setAttributes({ display_profile_text: value }); } 
				}),
				createElement(ToggleControl, {
					label: 'Profile Link', 
					checked: attributes.display_profile_link, 
					onChange: function(value) { setAttributes({ display_profile_link: value }); } 
				}),

				createElement( 'label', { className: 'discogs-label' }, 'Banner Aspect Ratio' ),
				createElement('div', {style:{display:'grid',gridTemplateColumns:'1fr 30px 1fr',gridGap:'6px'}},
					createElement(TextControl, {
						type: 'number',
						value: attributes.profile_banner_aspectRatioWidth,
						onChange: function(val) { setAttributes({ profile_banner_aspectRatioWidth: val }); }
					}),
					createElement('div',{style:{textAlign:'center',lineHeight:'30px'}},'x'),
					createElement(TextControl, {
						type: 'number',
						value: attributes.profile_banner_aspectRatioHeight,
						onChange: function(val) { setAttributes({ profile_banner_aspectRatioHeight: val }); }
					}),
				),
				createElement( 'label', { className: 'discogs-label' }, 'Banner Background Color' ),
				createElement(ColorPalette, {
					value: attributes.profile_banner_background_color,
					onChange: function (val) {
						setAttributes({ profile_banner_background_color: val });
					},
					disableAlpha: false
				}),	



				createElement( 'label', { className: 'discogs-label' }, 'Discogs Logo Color' ),
				createElement(ColorPalette, {
					value: attributes.profile_discogs_logo_color,
					onChange: function (val) {
						setAttributes({ profile_discogs_logo_color: val });
					},
					disableAlpha: false
				}),	
				createElement( 'label', { className: 'discogs-label' }, 'Profile Link Background Color' ),
				createElement(ColorPalette, {
					value: attributes.profile_link_background_color,
					onChange: function (val) {
						setAttributes({ profile_link_background_color: val });
					},
					disableAlpha: false
				}),	


			)
		);

		return createElement(Fragment,null,
			InspectorPanel,
			createElement('div', useBlockProps(),
				createElement(ServerSideRender, {
					block: "blocks-for-discogs/discogs-user-profile",
					httpMethod: "POST",
					attributes: attributes
				})
			)
		);

    },
    save: function () {
        return null; // Rendered server-side
    }
});
(function (blocks, element, blockEditor, components, serverSideRender, i18n) {
	const el = element.createElement;
	const __ = i18n.__;
	const InspectorControls = blockEditor.InspectorControls;
	const PanelBody = components.PanelBody;
	const ToggleControl = components.ToggleControl;
	const SelectControl = components.SelectControl;
	const useBlockProps = blockEditor.useBlockProps;

	blocks.registerBlockType('nextav/calendar-block', {
		title: __('Next Available Calendar', 'nextav'),
		icon: 'calendar',
		category: 'widgets',
		attributes: {
			style: {
				type: 'string',
				default: 'simple',
				enum: ['simple', 'badges', 'card']
			},
			showEventName: { type: 'boolean', default: true },
			showEventDetails: { type: 'boolean', default: true },
			colorEvents: { type: 'boolean', default: true },
			highlightAvailable: { type: 'boolean', default: true },
			showPast: { type: 'boolean', default: false },
			includeWeekends: { type: 'boolean', default: true }
		},

		edit: function (props) {
			const { attributes, setAttributes } = props;
			const blockProps = useBlockProps();

			return el(
				'div',
				blockProps,
				el(
					InspectorControls,
					{},
					el(
						PanelBody,
						{ title: __('Settings', 'nextav'), initialOpen: true },
						el(SelectControl, {
							label: __('Display Style', 'nextav'),
							value: attributes.style,
							options: [
								{ label: 'Simple', value: 'simple' },
								{ label: 'Badges', value: 'badges' },
								{ label: 'Card', value: 'card' }
							],
							onChange: (value) => setAttributes({ style: value }),
							__next40pxDefaultSize: true,
							__nextHasNoMarginBottom: true,
						}),
						el(ToggleControl, {
							label: __('Show Event Name', 'nextav'),
							checked: attributes.showEventName,
							onChange: (value) => setAttributes({ showEventName: value }),
							__nextHasNoMarginBottom: true,
						}),
						el(ToggleControl, {
							label: __('Show Event Details', 'nextav'),
							checked: attributes.showEventDetails,
							onChange: (value) => setAttributes({ showEventDetails: value }),
							__nextHasNoMarginBottom: true,
						}),
						el(ToggleControl, {
							label: __('Color Events', 'nextav'),
							checked: attributes.colorEvents,
							onChange: (value) => setAttributes({ colorEvents: value }),
							__nextHasNoMarginBottom: true,
						}),
						el(ToggleControl, {
							label: __('Highlight Available Date', 'nextav'),
							checked: attributes.highlightAvailable,
							onChange: (value) => setAttributes({ highlightAvailable: value }),
							__nextHasNoMarginBottom: true,
						}),
						el(ToggleControl, {
							label: __('Show Past Dates', 'nextav'),
							checked: attributes.showPast,
							onChange: (value) => setAttributes({ showPast: value }),
							__nextHasNoMarginBottom: true,
						}),
						el(ToggleControl, {
							label: __('Include Weekends', 'nextav'),
							checked: attributes.includeWeekends,
							onChange: (value) => setAttributes({ includeWeekends: value }),
							__nextHasNoMarginBottom: true,
						})
					)
				),
				el(serverSideRender, {
					block: 'nextav/calendar-block',
					attributes: attributes,
				})
			);
		},

		save: function () {
			return null; // Server-rendered block
		}
	});
})(
	window.wp.blocks,
	window.wp.element,
	window.wp.blockEditor,
	window.wp.components,
	window.wp.serverSideRender,
	window.wp.i18n
);

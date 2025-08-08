(function (blocks, element, blockEditor, components, serverSideRender, i18n) {
	const el = element.createElement;
	const __ = i18n.__;
	const InspectorControls = blockEditor.InspectorControls;
	const PanelBody = components.PanelBody;
	const TextControl = components.TextControl;
	const ToggleControl = components.ToggleControl;
	const SelectControl = components.SelectControl;
    const useBlockProps = blockEditor.useBlockProps;

	blocks.registerBlockType('nextav/date-block', {
		title: __('Next Available Date', 'nextav'),
		icon: 'calendar',
		category: 'widgets',
		attributes: {
			title: { type: 'string', default: '' },
			showUpdatedDate: { type: 'boolean', default: true },
			dateFormat: {
				type: 'string',
				default: 'F j, Y',
				enum: [
					'Y-m-d',
					'm/d/Y',
					'd/m/Y',
					'F j, Y',
					'j F Y',
					'D, M j, Y',
					'l, F j, Y'
				]
			},
			style: {
				type: 'string',
				default: 'simple',
				enum: ['simple', 'badges', 'card']
			},
            availableDateLabel: {
                type: 'string',
                default: 'Next Available:'
            },
            updatedDateLabel: {
                type: 'string',
                default: 'Last Updated:'
            }
		},

		edit: function (props) {
			const { attributes, setAttributes } = props;
            const blockProps = useBlockProps();

			return el(
				'div',
				blockProps, // ✅ this makes it selectable
				el(
					InspectorControls,
					{},
					el(
						PanelBody,
						{ title: __('Settings', 'nextav'), initialOpen: true },
						el(TextControl, {
							label: __('Title', 'nextav'),
							help: __('Optional title above the date', 'nextav'),
							value: attributes.title,
							onChange: (value) => setAttributes({ title: value }),
							__next40pxDefaultSize: true,
							__nextHasNoMarginBottom: true,
						}),
						el(ToggleControl, {
							label: __('Show Updated Date', 'nextav'),
							help: __('Include the last updated date?', 'nextav'),
							checked: attributes.showUpdatedDate,
							onChange: (value) => setAttributes({ showUpdatedDate: value }),
							__nextHasNoMarginBottom: true,
						}),
						el(SelectControl, {
							label: __('Date Format', 'nextav'),
							help: __('Choose how the date should be displayed.', 'nextav'),
							value: attributes.dateFormat,
							options: [
								{ label: 'July 1, 2025 (Month Day, Year)', value: 'F j, Y' },
								{ label: '2025-07-01 (ISO 8601)', value: 'Y-m-d' },
								{ label: '07/01/2025 (MM/DD/YYYY)', value: 'm/d/Y' },
								{ label: '01/07/2025 (DD/MM/YYYY)', value: 'd/m/Y' },
								{ label: '1 July 2025 (Day Month Year)', value: 'j F Y' },
								{ label: 'Tue, Jul 1, 2025 (Short weekday)', value: 'D, M j, Y' },
								{ label: 'Tuesday, July 1, 2025 (Full weekday)', value: 'l, F j, Y' }
							],
							onChange: (value) => setAttributes({ dateFormat: value }),
							__next40pxDefaultSize: true,
							__nextHasNoMarginBottom: true,
						}),
						el(SelectControl, {
							label: __('Display Style', 'nextav'),
							help: __('Choose a visual style for the block.', 'nextav'),
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
						el(TextControl, {
							label: __('Available Date Label', 'nextav'),
							help: __('Optional label before the next available date', 'nextav'),
							value: attributes.availableDateLabel,
							onChange: (value) => setAttributes({ availableDateLabel: value }),
							__next40pxDefaultSize: true,
							__nextHasNoMarginBottom: true,
						}),
						el(TextControl, {
							label: __('Updated Date Label', 'nextav'),
							help: __('Optional label before the last updated date', 'nextav'),
							value: attributes.updatedDateLabel,
							onChange: (value) => setAttributes({ updatedDateLabel: value }),
							__next40pxDefaultSize: true,
							__nextHasNoMarginBottom: true,
						})
					)
				),
				el(serverSideRender, {
					block: 'nextav/date-block',
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

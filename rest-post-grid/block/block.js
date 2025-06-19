(function(wp){
    const { registerBlockType } = wp.blocks;
    const { TextControl, PanelBody, SelectControl, RangeControl, ToggleControl } = wp.components;
    const { InspectorControls, useBlockProps } = wp.blockEditor;
    const ServerSideRender = wp.serverSideRender;

    registerBlockType('danko-rest-post-grid/block', {
        title: 'Danko REST Post Grid',
        icon: 'grid-view',
        category: 'widgets',
        attributes: {
            api_url: { type: 'string', default: '' },
            layout: { type: 'string', default: 'grid' },
            columns: { type: 'number', default: 4 },
            font_family: { type: 'string', default: 'inherit' },
            font_size: { type: 'string', default: 'inherit' },
            link_color: { type: 'string', default: '#000000' },
            link_size: { type: 'string', default: 'inherit' },
            link_family: { type: 'string', default: 'inherit' },
            icon_color: { type: 'string', default: '#ffffff' },
            icon_size: { type: 'string', default: '32px' },
            overlay_title: { type: 'boolean', default: false },
            line_height: { type: 'string', default: '1.2' },
            text_shadow: { type: 'string', default: 'none' },
            box_shadow: { type: 'string', default: 'none' },
            border_radius: { type: 'string', default: '0' },
            padding: { type: 'string', default: '10px' },
            margin: { type: 'string', default: '0' },
            title_padding: { type: 'string', default: '0' },
            title_margin: { type: 'string', default: '0' },
            link_decoration: { type: 'string', default: 'none' },
            title_align: { type: 'string', default: 'center' },
           order_by: { type: 'string', default: 'date' },
            auto_fetch: { type: 'boolean', default: true },
            refresh_interval: { type: 'number', default: 60 },
            show_meta: { type: 'boolean', default: false }
        },
        edit: ({ attributes, setAttributes }) => {
            const blockProps = useBlockProps();
            if(!attributes.api_url && window.drpg_defaults){
                setAttributes({ api_url: window.drpg_defaults.api_url || '' });
            }
            return (
                wp.element.createElement('div', blockProps,
                    wp.element.createElement(InspectorControls, null,
                        wp.element.createElement(PanelBody, { title: 'Layout', initialOpen: true },
                            wp.element.createElement(TextControl, {
                                label: 'API URL',
                                value: attributes.api_url,
                                onChange: (value) => setAttributes({ api_url: value })
                            }),
                            wp.element.createElement(SelectControl, {
                                label: 'Layout',
                                value: attributes.layout,
                                options: [
                                    { label: 'Grid', value: 'grid' },
                                    { label: 'List', value: 'list' }
                                ],
                                onChange: (value) => setAttributes({ layout: value })
                            }),
                            wp.element.createElement(RangeControl, {
                                label: 'Columns',
                                value: attributes.columns,
                                min: 1,
                                max: 6,
                                onChange: (value) => setAttributes({ columns: value })
                            }),
                            wp.element.createElement(SelectControl, {
                                label: 'Order By',
                                value: attributes.order_by,
                                options: [
                                    { label: 'Date', value: 'date' },
                                    { label: 'Alphabetical', value: 'title' }
                                ],
                                onChange: (value) => setAttributes({ order_by: value })
                            }),
                            wp.element.createElement(ToggleControl, {
                                label: 'Show Meta',
                                checked: attributes.show_meta,
                                onChange: (value) => setAttributes({ show_meta: value })
                            })
                        ),
                        wp.element.createElement(PanelBody, { title: 'Typography', initialOpen: false },
                            wp.element.createElement(SelectControl, {
                                label: 'Font Family',
                                value: attributes.font_family,
                                options: [
                                    { label: 'Inherit', value: 'inherit' },
                                    { label: 'Arial', value: 'Arial, Helvetica, sans-serif' },
                                    { label: 'Georgia', value: 'Georgia, serif' },
                                    { label: 'Courier New', value: '"Courier New", monospace' }
                                ],
                                onChange: (value) => setAttributes({ font_family: value })
                            }),
                            wp.element.createElement(RangeControl, {
                                label: 'Font Size',
                                value: parseInt(attributes.font_size) || 16,
                                min: 10,
                                max: 40,
                                onChange: (value) => setAttributes({ font_size: value + 'px' })
                            }),
                            wp.element.createElement(RangeControl, {
                                label: 'Line Height',
                                value: parseFloat(attributes.line_height),
                                min: 1,
                                max: 2,
                                step: 0.1,
                                onChange: (value) => setAttributes({ line_height: value.toString() })
                            }),
                            wp.element.createElement(SelectControl, {
                                label: 'Title Align',
                                value: attributes.title_align,
                                options: [
                                    { label: 'Left', value: 'left' },
                                    { label: 'Center', value: 'center' },
                                    { label: 'Right', value: 'right' }
                                ],
                                onChange: (value) => setAttributes({ title_align: value })
                            }),
                            wp.element.createElement(SelectControl, {
                                label: 'Link Font Family',
                                value: attributes.link_family,
                                options: [
                                    { label: 'Inherit', value: 'inherit' },
                                    { label: 'Arial', value: 'Arial, Helvetica, sans-serif' },
                                    { label: 'Georgia', value: 'Georgia, serif' },
                                    { label: 'Courier New', value: '"Courier New", monospace' }
                                ],
                                onChange: (value) => setAttributes({ link_family: value })
                            }),
                            wp.element.createElement(RangeControl, {
                                label: 'Link Size',
                                value: parseInt(attributes.link_size) || 16,
                                min: 10,
                                max: 40,
                                onChange: (value) => setAttributes({ link_size: value + 'px' })
                            })
                        ),
                        wp.element.createElement(PanelBody, { title: 'Style', initialOpen: false },
                            wp.element.createElement(TextControl, {
                                label: 'Link Color',
                                value: attributes.link_color,
                                onChange: (value) => setAttributes({ link_color: value })
                            }),
                            wp.element.createElement(SelectControl, {
                                label: 'Link Decoration',
                                value: attributes.link_decoration,
                                options: [
                                    { label: 'None', value: 'none' },
                                    { label: 'Underline', value: 'underline' },
                                    { label: 'Overline', value: 'overline' },
                                    { label: 'Line Through', value: 'line-through' }
                                ],
                                onChange: (value) => setAttributes({ link_decoration: value })
                            }),
                            wp.element.createElement(TextControl, {
                                label: 'Icon Color',
                                value: attributes.icon_color,
                                onChange: (value) => setAttributes({ icon_color: value })
                            }),
                            wp.element.createElement(RangeControl, {
                                label: 'Icon Size',
                                value: parseInt(attributes.icon_size) || 32,
                                min: 16,
                                max: 128,
                                onChange: (value) => setAttributes({ icon_size: value + 'px' })
                            }),
                            wp.element.createElement(TextControl, {
                                label: 'Text Shadow',
                                value: attributes.text_shadow,
                                onChange: (value) => setAttributes({ text_shadow: value })
                            }),
                            wp.element.createElement(TextControl, {
                                label: 'Box Shadow',
                                value: attributes.box_shadow,
                                onChange: (value) => setAttributes({ box_shadow: value })
                            }),
                            wp.element.createElement(TextControl, {
                                label: 'Border Radius',
                                value: attributes.border_radius,
                                onChange: (value) => setAttributes({ border_radius: value })
                            }),
                            wp.element.createElement(TextControl, {
                                label: 'Padding',
                                value: attributes.padding,
                                onChange: (value) => setAttributes({ padding: value })
                            }),
                            wp.element.createElement(TextControl, {
                                label: 'Margin',
                                value: attributes.margin,
                                onChange: (value) => setAttributes({ margin: value })
                            }),
                            wp.element.createElement(TextControl, {
                                label: 'Title Padding',
                                value: attributes.title_padding,
                                onChange: (value) => setAttributes({ title_padding: value })
                            }),
                            wp.element.createElement(TextControl, {
                                label: 'Title Margin',
                                value: attributes.title_margin,
                                onChange: (value) => setAttributes({ title_margin: value })
                            }),
                            wp.element.createElement(ToggleControl, {
                                label: 'Overlay Title',
                                checked: attributes.overlay_title,
                                onChange: (value) => setAttributes({ overlay_title: value })
                            }),
                            wp.element.createElement(ToggleControl, {
                                label: 'Auto Fetch',
                                checked: attributes.auto_fetch,
                                onChange: (value) => setAttributes({ auto_fetch: value })
                            }),
                            wp.element.createElement(TextControl, {
                                label: 'Refresh Interval (sec)',
                                value: attributes.refresh_interval,
                                onChange: (value) => setAttributes({ refresh_interval: parseInt(value) || 0 })
                            })
                        )
                    ),
                    wp.element.createElement(ServerSideRender, {
                        block: 'danko-rest-post-grid/block',
                        attributes: attributes
                    })
                )
            );
        },
        save: () => null
    });
})(window.wp);

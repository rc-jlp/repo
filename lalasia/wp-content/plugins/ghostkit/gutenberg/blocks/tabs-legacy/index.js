// External Dependencies.
import classnames from 'classnames/dedupe';

// Internal Dependencies.
import getIcon from '../../utils/get-icon';

import deprecatedArray from './deprecated';

const { applyFilters } = wp.hooks;
const { __, sprintf } = wp.i18n;
const { Component, Fragment } = wp.element;
const { PanelBody, RangeControl, SelectControl } = wp.components;

const { RichText, InspectorControls, InnerBlocks } = wp.blockEditor;

/**
 * Returns the layouts configuration for a given number of tabs.
 *
 * @param {number} attributes tabs attributes.
 *
 * @return {Object[]} Tabs layout configuration.
 */
const getTabsTemplate = (attributes) => {
  const { tabsCount } = attributes;
  const result = [];

  for (let k = 1; k <= tabsCount; k += 1) {
    result.push(['ghostkit/tabs-tab', { tabNumber: k }]);
  }

  return result;
};

const getTabs = ({ tabsCount, tabsSettings }) => {
  const result = [];

  for (let k = 1; k <= tabsCount; k += 1) {
    result.push({
      label: tabsSettings[`tab_${k}`]
        ? tabsSettings[`tab_${k}`].label
        : sprintf(__('Tab %d', 'ghostkit'), k),
      number: k,
    });
  }

  return result;
};

class TabsBlock extends Component {
  render() {
    const { attributes, setAttributes } = this.props;

    let { className = '' } = this.props;

    const { tabsCount, tabActive, tabsSettings, buttonsAlign } = attributes;

    const tabs = getTabs(attributes);

    className = classnames(className, 'ghostkit-tabs ghostkit-tabs-legacy');

    className = applyFilters('ghostkit.editor.className', className, this.props);

    return (
      <Fragment>
        <InspectorControls>
          <PanelBody>
            <div className="ghostkit-alert" style={{ borderLeftColor: '#de9116' }}>
              <div className="ghostkit-alert-content">
                {__(
                  'This Tabs block has a legacy structure. To use new tabs, add it again from blocks inserter.',
                  'ghostkit'
                )}
              </div>
            </div>
          </PanelBody>
          <PanelBody>
            <RangeControl
              label={__('Tabs', 'ghostkit')}
              value={tabsCount}
              onChange={(value) => setAttributes({ tabsCount: value })}
              min={1}
              max={6}
            />
            <SelectControl
              label={__('Tabs align', 'ghostkit')}
              value={buttonsAlign}
              options={[
                {
                  value: 'start',
                  label: __('Start', 'ghostkit'),
                },
                {
                  value: 'center',
                  label: __('Center', 'ghostkit'),
                },
                {
                  value: 'end',
                  label: __('End', 'ghostkit'),
                },
              ]}
              onChange={(value) => setAttributes({ buttonsAlign: value })}
            />
          </PanelBody>
        </InspectorControls>
        <div className={className} data-tab-active={tabActive}>
          <div
            className={classnames(
              'ghostkit-tabs-buttons',
              `ghostkit-tabs-buttons-align-${buttonsAlign}`
            )}
          >
            {tabs.map((val) => {
              const selected = tabActive === val.number;

              return (
                <RichText
                  tagName="div"
                  data-tab={val.number}
                  className={classnames(
                    'ghostkit-tabs-buttons-item',
                    selected ? 'ghostkit-tabs-buttons-item-active' : ''
                  )}
                  placeholder={__('Tab label', 'ghostkit')}
                  value={val.label}
                  unstableOnFocus={() => setAttributes({ tabActive: val.number })}
                  onChange={(value) => {
                    if ('undefined' !== typeof tabs[val.number - 1]) {
                      if ('undefined' === typeof tabsSettings[`tab_${val.number}`]) {
                        tabsSettings[`tab_${val.number}`] = {};
                      }
                      tabsSettings[`tab_${val.number}`].label = value;
                      setAttributes({ tabsSettings: { ...tabsSettings } });
                    }
                  }}
                  withoutInteractiveFormatting
                  keepPlaceholderOnFocus
                  key={`tab_button_${val.number}`}
                />
              );
            })}
          </div>
          <div className="ghostkit-tabs-content">
            <InnerBlocks
              template={getTabsTemplate(attributes)}
              templateLock="all"
              allowedBlocks={['ghostkit/tabs-tab']}
            />
          </div>
        </div>
      </Fragment>
    );
  }
}

export const name = 'ghostkit/tabs';

export const settings = {
  title: __('Tabs (legacy)', 'ghostkit'),
  description: __('Tabs.', 'ghostkit'),
  icon: getIcon('block-tabs', true),
  category: 'design',
  keywords: [__('tabs', 'ghostkit'), __('tab', 'ghostkit')],
  ghostkit: {
    supports: {
      styles: true,
      spacings: true,
      display: true,
      scrollReveal: true,
      customCSS: true,
    },
  },
  supports: {
    inserter: false,
    html: false,
    className: false,
    anchor: true,
    align: ['wide', 'full'],
  },
  attributes: {
    tabsCount: {
      type: 'number',
      default: 2,
    },
    tabActive: {
      type: 'number',
      default: 1,
    },
    tabsSettings: {
      type: 'object',
      default: {},
    },
    buttonsAlign: {
      type: 'string',
      default: 'start',
    },
  },

  edit: TabsBlock,

  save(props) {
    const { tabsCount, tabActive, buttonsAlign } = props.attributes;

    let className = `ghostkit-tabs ghostkit-tabs-${tabsCount}`;

    className = applyFilters('ghostkit.blocks.className', className, {
      ...{
        name,
      },
      ...props,
    });

    const tabs = getTabs(props.attributes);

    return (
      <div className={className} data-tab-active={tabActive}>
        <div
          className={classnames(
            'ghostkit-tabs-buttons',
            `ghostkit-tabs-buttons-align-${buttonsAlign}`
          )}
        >
          {tabs.map((val) => (
            <RichText.Content
              tagName="a"
              data-tab={val.number}
              href={`#tab-${val.number}`}
              className="ghostkit-tabs-buttons-item"
              key={`tab_button_${val.number}`}
              value={val.label}
            />
          ))}
        </div>
        <div className="ghostkit-tabs-content">
          <InnerBlocks.Content />
        </div>
      </div>
    );
  },

  deprecated: deprecatedArray,
};

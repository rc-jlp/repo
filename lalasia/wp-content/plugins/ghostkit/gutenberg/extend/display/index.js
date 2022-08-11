/**
 * Internal dependencies
 */
import checkCoreBlock from '../check-core-block';
import {
  getActiveClass,
  replaceClass,
  addClass,
  removeClass,
  hasClass,
} from '../../utils/classes-replacer';
import ResponsiveTabPanel from '../../components/responsive-tab-panel';
import getIcon from '../../utils/get-icon';
import ActiveIndicator from '../../components/active-indicator';
import ToggleGroup from '../../components/toggle-group';

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

const { applyFilters, addFilter } = wp.hooks;

const { Component, Fragment } = wp.element;

const { hasBlockSupport } = wp.blocks;

const { createHigherOrderComponent } = wp.compose;

const { InspectorControls } = wp.blockEditor;

const { PanelBody } = wp.components;

const { GHOSTKIT, ghostkitVariables } = window;

let initialOpenPanel = false;

/**
 * Get array for Select element.
 *
 * @param {String} screen - screen size
 *
 * @returns {Array} array for Select.
 */
const getDefaultDisplay = function (screen = '') {
  return [
    {
      label: 'all' === screen ? __('Default', 'ghostkit') : __('Inherit', 'ghostkit'),
      value: '',
    },
    {
      label: __('Show', 'ghostkit'),
      value: 'block',
    },
    {
      label: __('Hide', 'ghostkit'),
      value: 'none',
    },
  ];
};

/**
 * Check if block Display allowed.
 *
 * @param {object} data - block data.
 * @return {boolean} allowed Display.
 */
function allowedDisplay(data) {
  let allow = false;
  const checkSupportVar = data && data.ghostkit && data.ghostkit.supports ? data : data.name;

  if (
    hasBlockSupport(checkSupportVar, 'customClassName', true) &&
    GHOSTKIT.hasBlockSupport(checkSupportVar, 'display', false)
  ) {
    allow = true;
  }

  if (!allow) {
    allow =
      data &&
      data.attributes &&
      applyFilters('ghostkit.blocks.allowDisplay', checkCoreBlock(data.name), data, data.name);
  }

  return allow;
}

/**
 * Get current display for selected screen size.
 *
 * @param {String} className - block className.
 * @param {String} screen - name of screen size.
 *
 * @returns {String} display value.
 */
function getCurrentDisplay(className, screen) {
  if (!screen || 'all' === screen) {
    if (hasClass(className, 'ghostkit-d-none')) {
      return 'none';
    }
    if (hasClass(className, 'ghostkit-d-block')) {
      return 'block';
    }
  }

  return getActiveClass(className, `ghostkit-d-${screen}`, true);
}

/**
 * Override the default edit UI to include a new block inspector control for
 * assigning the custom display if needed.
 *
 * @param {function|Component} BlockEdit Original component.
 *
 * @return {string} Wrapped component.
 */
const withInspectorControl = createHigherOrderComponent((OriginalComponent) => {
  class GhostKitDisplayWrapper extends Component {
    constructor(props) {
      super(props);

      this.updateDisplay = this.updateDisplay.bind(this);
    }

    /**
     * Update display object.
     *
     * @param {String} screen - name of screen size.
     * @param {String} val - value for new display.
     */
    updateDisplay(screen, val) {
      const { attributes, setAttributes } = this.props;

      const { className } = attributes;

      let newClassName = className;

      if (screen && 'all' !== screen) {
        newClassName = replaceClass(newClassName, `ghostkit-d-${screen}`, val);
      } else {
        newClassName = removeClass(newClassName, 'ghostkit-d-none');
        newClassName = removeClass(newClassName, 'ghostkit-d-block');

        if (val) {
          newClassName = addClass(newClassName, `ghostkit-d-${val}`);
        }
      }

      setAttributes({
        className: newClassName,
      });
    }

    render() {
      const { props } = this;
      const { className } = props.attributes;

      const allow = allowedDisplay(props);

      if (!allow) {
        return <OriginalComponent {...props} />;
      }

      const filledTabs = {};
      if (
        ghostkitVariables &&
        ghostkitVariables.media_sizes &&
        Object.keys(ghostkitVariables.media_sizes).length
      ) {
        ['all', ...Object.keys(ghostkitVariables.media_sizes)].forEach((media) => {
          filledTabs[media] = !!getCurrentDisplay(className, media);
        });
      }

      // add new display controls.
      return (
        <Fragment>
          <OriginalComponent {...props} setState={this.setState} />
          <InspectorControls>
            <PanelBody
              title={
                <Fragment>
                  <span className="ghostkit-ext-icon">{getIcon('extension-display')}</span>
                  <span>{__('Display', 'ghostkit')}</span>
                  {className && getActiveClass(className, 'ghostkit-d') ? <ActiveIndicator /> : ''}
                </Fragment>
              }
              initialOpen={initialOpenPanel}
              onToggle={() => {
                initialOpenPanel = !initialOpenPanel;
              }}
            >
              <ResponsiveTabPanel filledTabs={filledTabs}>
                {(tabData) => (
                  <ToggleGroup
                    value={getCurrentDisplay(className, tabData.name)}
                    options={getDefaultDisplay(tabData.name).map((val) => ({
                      value: val.value,
                      label: val.label,
                    }))}
                    onChange={(value) => {
                      this.updateDisplay(tabData.name, value);
                    }}
                  />
                )}
              </ResponsiveTabPanel>
            </PanelBody>
          </InspectorControls>
        </Fragment>
      );
    }
  }

  return GhostKitDisplayWrapper;
}, 'withInspectorControl');

// Init filters.
addFilter('editor.BlockEdit', 'ghostkit/display/additional-attributes', withInspectorControl);

/* eslint-disable max-classes-per-file */
/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * Internal dependencies
 */
import checkCoreBlock from '../check-core-block';
import getIcon from '../../utils/get-icon';
import ActiveIndicator from '../../components/active-indicator';
import ToggleGroup from '../../components/toggle-group';

import parseSRConfig from './parseSRConfig';

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

const { applyFilters, addFilter } = wp.hooks;

const { Component, Fragment } = wp.element;

const { createHigherOrderComponent } = wp.compose;

const { InspectorControls, BlockControls } = wp.blockEditor;

const {
  BaseControl,
  PanelBody,
  TextControl,
  Button,
  ToolbarGroup,
  DropdownMenu,
  MenuGroup,
  MenuItem,
} = wp.components;

const $ = window.jQuery;

const { GHOSTKIT, ScrollReveal } = window;

let initialOpenPanel = false;

/**
 * Check if block SR allowed.
 *
 * @param {object} data - block data.
 * @return {boolean} allowed SR.
 */
function allowedSR(data) {
  let allow = false;
  const checkSupportVar = data && data.ghostkit && data.ghostkit.supports ? data : data.name;

  if (GHOSTKIT.hasBlockSupport(checkSupportVar, 'scrollReveal', false)) {
    allow = true;
  }

  if (!allow) {
    allow =
      data &&
      data.attributes &&
      applyFilters('ghostkit.blocks.allowScrollReveal', checkCoreBlock(data.name), data, data.name);
  }

  return allow;
}

/**
 * Extend ghostkit block attributes with SR.
 *
 * @param {Object} settings Original block settings.
 *
 * @return {Object} Filtered block settings.
 */
function addAttribute(settings) {
  const allow = allowedSR(settings);

  if (allow) {
    if (!settings.attributes.ghostkitSR) {
      settings.attributes.ghostkitSR = {
        type: 'string',
        default: '',
      };

      // add to deprecated items.
      if (settings.deprecated && settings.deprecated.length) {
        settings.deprecated.forEach((item, i) => {
          if (settings.deprecated[i].attributes) {
            settings.deprecated[i].attributes.ghostkitSR = settings.attributes.ghostkitSR;
          }
        });
      }
    }
  }
  return settings;
}

/**
 * Override the default edit UI to include a new block inspector control for
 * assigning the custom attribute if needed.
 *
 * @param {function|Component} BlockEdit Original component.
 *
 * @return {string} Wrapped component.
 */
const withInspectorControl = createHigherOrderComponent((OriginalComponent) => {
  class GhostKitSRWrapper extends Component {
    constructor(props) {
      super(props);

      const { ghostkitSR = '' } = this.props.attributes;

      const state = {
        effect: '',
        direction: '',
        distance: 50,
        scale: 0.9,
        duration: 900,
        delay: 0,
      };

      // parse data from string.
      // fade-right;duration:500;delay:1000;distance:60px;scale:0.8
      const data = ghostkitSR.split(';');

      let effect = data[0];
      if (effect) {
        let direction = effect.split('-');
        if (2 === direction.length) {
          // eslint-disable-next-line prefer-destructuring
          effect = direction[0];
          // eslint-disable-next-line prefer-destructuring
          direction = direction[1];
        } else {
          direction = '';
        }

        state.effect = effect;
        state.direction = direction;

        // replace other data config.
        if (1 < data.length) {
          data.forEach((item) => {
            const itemData = item.split(':');
            if (2 === itemData.length) {
              const name = itemData[0];
              const val = itemData[1];
              state[name] = val;
            }
          });
        }

        state.distance = parseFloat(state.distance);
        state.scale = parseFloat(state.scale);
        state.duration = parseFloat(state.duration);
        state.delay = parseFloat(state.delay);
      }

      this.state = state;

      this.updateData = this.updateData.bind(this);
    }

    updateData(newData) {
      const { ghostkitSR } = this.props.attributes;

      const { setAttributes } = this.props;

      const newState = { ...this.state, ...newData };

      let newAttribute = '';
      if (newState.effect) {
        newAttribute = newState.effect;

        if (newState.direction) {
          newAttribute += `-${newState.direction}`;

          if (50 !== newState.distance) {
            newAttribute += `;distance:${newState.distance}px`;
          }
        }
        if (900 !== newState.duration) {
          newAttribute += `;duration:${newState.duration}`;
        }
        if (0 !== newState.delay) {
          newAttribute += `;delay:${newState.delay}`;
        }

        if ('zoom' === newState.effect && 0.9 !== newState.scale) {
          newAttribute += `;scale:${newState.scale}`;
        }
      }

      this.setState(newData);

      if (ghostkitSR !== newAttribute) {
        setAttributes({ ghostkitSR: newAttribute });
      }
    }

    render() {
      const { props } = this;
      const allow = allowedSR(props);

      if (!allow) {
        return <OriginalComponent {...props} />;
      }

      const { ghostkitSR } = props.attributes;

      const presetAnimations = [
        {
          label: __('Fade in', 'ghostkit'),
          icon: getIcon('sr-fade'),
          value: 'fade',
          onClick: () => this.updateData({ effect: 'fade', direction: '' }),
        },
        {
          label: __('Zoom in', 'ghostkit'),
          icon: getIcon('sr-zoom'),
          value: 'zoom',
          onClick: () => this.updateData({ effect: 'zoom', direction: '' }),
        },
        {
          label: __('Zoom in from Bottom', 'ghostkit'),
          icon: getIcon('sr-zoom-from-bottom'),
          value: 'zoom-up',
          onClick: () => this.updateData({ effect: 'zoom', direction: 'up' }),
        },
        {
          label: __('Zoom in from Left', 'ghostkit'),
          icon: getIcon('sr-zoom-from-left'),
          value: 'zoom-left',
          onClick: () => this.updateData({ effect: 'zoom', direction: 'left' }),
        },
        {
          label: __('Zoom in from Right', 'ghostkit'),
          icon: getIcon('sr-zoom-from-right'),
          value: 'zoom-right',
          onClick: () => this.updateData({ effect: 'zoom', direction: 'right' }),
        },
      ];

      // add new SR controls.
      return (
        <Fragment>
          <OriginalComponent {...props} setState={this.setState} />
          <BlockControls group="other">
            <ToolbarGroup>
              <DropdownMenu
                icon={getIcon('extension-sr')}
                label={__('Animate on Scroll', 'ghostkit')}
                menuProps={{
                  className: 'ghostkit-control-sr-presets-menu',
                }}
                toggleProps={{
                  className: classnames(
                    'ghostkit-control-sr-presets-toggle',
                    ghostkitSR ? 'ghostkit-control-sr-presets-toggle-active' : ''
                  ),
                }}
              >
                {() => (
                  <Fragment>
                    <MenuGroup>
                      {presetAnimations.map((animationItem) => (
                        <MenuItem
                          icon={animationItem.icon}
                          isSelected={ghostkitSR === animationItem.value}
                          key={`animation-${animationItem.value}`}
                          label={animationItem.label}
                          onClick={() => animationItem.onClick()}
                          role="menuitemradio"
                        >
                          {animationItem.label}
                        </MenuItem>
                      ))}
                    </MenuGroup>
                    {ghostkitSR && (
                      <MenuGroup>
                        <MenuItem
                          isSelected={false}
                          label={__('Remove animation', 'ghostkit')}
                          onClick={() => this.updateData({ effect: '' })}
                        >
                          {__('Remove animation', 'ghostkit')}
                        </MenuItem>
                      </MenuGroup>
                    )}
                    <MenuGroup>
                      {__(
                        'Advanced options are placed in the block settings panel.',
                        'ghostkit'
                      )}
                    </MenuGroup>
                  </Fragment>
                )}
              </DropdownMenu>
            </ToolbarGroup>
          </BlockControls>
          <InspectorControls>
            <PanelBody
              title={
                <Fragment>
                  <span className="ghostkit-ext-icon">{getIcon('extension-sr')}</span>
                  <span>{__('Animate on Scroll', 'ghostkit')}</span>
                  {ghostkitSR ? <ActiveIndicator /> : ''}
                </Fragment>
              }
              initialOpen={initialOpenPanel}
              onToggle={() => {
                initialOpenPanel = !initialOpenPanel;
              }}
            >
              <ToggleGroup
                value={this.state.effect}
                options={[
                  {
                    label: __('Fade', 'ghostkit'),
                    value: 'fade',
                  },
                  {
                    label: __('Zoom', 'ghostkit'),
                    value: 'zoom',
                  },
                ]}
                onChange={(value) => {
                  this.updateData({ effect: value });
                }}
                allowReset
              />

              {this.state.effect ? (
                <Fragment>
                  <BaseControl className="ghostkit-control-sr-direction">
                    <div className="ghostkit-control-sr-direction-wrap">
                      <div className="ghostkit-control-sr-direction-left">
                        <Button
                          className={
                            'left' === this.state.direction
                              ? 'ghostkit-control-sr-direction-active'
                              : ''
                          }
                          onClick={() => this.updateData({ direction: 'left' })}
                        >
                          {getIcon('icon-arrow-right')}
                        </Button>
                      </div>
                      <div className="ghostkit-control-sr-direction-top">
                        <Button
                          className={
                            'down' === this.state.direction
                              ? 'ghostkit-control-sr-direction-active'
                              : ''
                          }
                          onClick={() => this.updateData({ direction: 'down' })}
                        >
                          {getIcon('icon-arrow-down')}
                        </Button>
                      </div>
                      <div className="ghostkit-control-sr-direction-right">
                        <Button
                          className={
                            'right' === this.state.direction
                              ? 'ghostkit-control-sr-direction-active'
                              : ''
                          }
                          onClick={() => this.updateData({ direction: 'right' })}
                        >
                          {getIcon('icon-arrow-left')}
                        </Button>
                      </div>
                      <div className="ghostkit-control-sr-direction-bottom">
                        <Button
                          className={
                            'up' === this.state.direction
                              ? 'ghostkit-control-sr-direction-active'
                              : ''
                          }
                          onClick={() => this.updateData({ direction: 'up' })}
                        >
                          {getIcon('icon-arrow-up')}
                        </Button>
                      </div>
                      <div className="ghostkit-control-sr-direction-center">
                        <Button
                          className={
                            !this.state.direction ? 'ghostkit-control-sr-direction-active' : ''
                          }
                          onClick={() => this.updateData({ direction: '' })}
                        >
                          {getIcon('icon-circle')}
                        </Button>
                      </div>
                    </div>
                  </BaseControl>

                  {this.state.direction || 'zoom' === this.state.effect ? (
                    <div className="ghostkit-grid-controls">
                      {this.state.direction ? (
                        <TextControl
                          type="number"
                          label={__('Distance', 'ghostkit')}
                          value={this.state.distance}
                          onChange={(value) => this.updateData({ distance: value })}
                          min={10}
                          max={200}
                        />
                      ) : (
                        ''
                      )}
                      {'zoom' === this.state.effect ? (
                        <TextControl
                          type="number"
                          label={__('Scale', 'ghostkit')}
                          value={this.state.scale}
                          onChange={(value) => this.updateData({ scale: value })}
                          min={0}
                          max={1}
                          step={0.05}
                        />
                      ) : (
                        ''
                      )}
                      {!this.state.direction || 'zoom' !== this.state.effect ? <div /> : ''}
                    </div>
                  ) : (
                    ''
                  )}

                  <div className="ghostkit-grid-controls">
                    <TextControl
                      type="number"
                      label={__('Duration', 'ghostkit')}
                      value={this.state.duration}
                      onChange={(value) => this.updateData({ duration: value })}
                      min={100}
                      max={2000}
                    />
                    <TextControl
                      type="number"
                      label={__('Delay', 'ghostkit')}
                      value={this.state.delay}
                      onChange={(value) => this.updateData({ delay: value })}
                      min={0}
                      max={1000}
                    />
                  </div>
                </Fragment>
              ) : (
                ''
              )}
            </PanelBody>
          </InspectorControls>
        </Fragment>
      );
    }
  }

  return GhostKitSRWrapper;
}, 'withInspectorControl');

/**
 * Override props assigned to save component to inject custom styles.
 * This is only applied if the block's save result is an
 * element and not a markup string.
 *
 * @param {Object} extraProps Additional props applied to save element.
 * @param {Object} blockType  Block type.
 * @param {Object} attributes Current block attributes.
 *
 * @return {Object} Filtered props applied to save element.
 */
function addSaveProps(extraProps, blockType, attributes) {
  if (attributes.ghostkitSR) {
    extraProps['data-ghostkit-sr'] = attributes.ghostkitSR;
  }

  return extraProps;
}

const withDataSR = createHigherOrderComponent((BlockListBlock) => {
  class GhostKitSRWrapper extends Component {
    constructor(props) {
      super(props);

      this.state = {
        allowedSR: allowedSR(this.props),
        currentSR: '',
      };

      this.runSRTimeout = false;

      this.maybeRunSR = this.maybeRunSR.bind(this);
    }

    componentDidMount() {
      this.maybeRunSR();
    }

    componentDidUpdate() {
      this.maybeRunSR();
    }

    maybeRunSR() {
      const { attributes } = this.props;

      if (!this.state.allowedSR || this.state.currentSR === attributes.ghostkitSR) {
        return;
      }

      if (this.props && this.props.clientId) {
        this.setState({
          currentSR: attributes.ghostkitSR,
        });

        clearTimeout(this.runSRTimeout);

        this.runSRTimeout = setTimeout(() => {
          const $element = $(`[id="block-${this.props.clientId}"]`);
          const element = $element[0];

          if (element) {
            const config = parseSRConfig(attributes.ghostkitSR);

            config.container = '.editor-styles-wrapper';

            ScrollReveal().clean(element);
            ScrollReveal().reveal(element, config);
          }
        }, 150);
      }
    }

    render() {
      return <BlockListBlock {...this.props} />;
    }
  }

  return GhostKitSRWrapper;
}, 'withDataSR');

// Init filters.
addFilter('blocks.registerBlockType', 'ghostkit/sr/additional-attributes', addAttribute);
addFilter('editor.BlockEdit', 'ghostkit/sr/additional-attributes', withInspectorControl);
addFilter('blocks.getSaveContent.extraProps', 'ghostkit/sr/save-props', addSaveProps);
addFilter('editor.BlockListBlock', 'ghostkit/sr/editor/additional-attributes', withDataSR);

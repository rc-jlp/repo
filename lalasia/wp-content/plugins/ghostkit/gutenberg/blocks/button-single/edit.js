/**
 * External dependencies
 */
import classnames from 'classnames/dedupe';

/**
 * Internal dependencies
 */
import ColorPicker from '../../components/color-picker';
import IconPicker from '../../components/icon-picker';
import ColorIndicator from '../../components/color-indicator';
import ApplyFilters from '../../components/apply-filters';
import URLPicker from '../../components/url-picker';
import ToggleGroup from '../../components/toggle-group';

/**
 * WordPress dependencies
 */
const { applyFilters } = wp.hooks;

const { __ } = wp.i18n;

const { Component, Fragment } = wp.element;

const { SelectControl, PanelBody, RangeControl, TabPanel, ToggleControl } = wp.components;

const { InspectorControls, RichText } = wp.blockEditor;

/**
 * Block Edit Class.
 */
class BlockEdit extends Component {
  constructor(props) {
    super(props);

    this.state = {
      selectedColorState: 'normal',
    };
  }

  componentDidUpdate(prevProps) {
    const { isSelected } = this.props;

    // Reset selected color state when block is not selected.
    if (prevProps.isSelected && !isSelected) {
      // eslint-disable-next-line react/no-did-update-set-state
      this.setState({
        selectedColorState: 'normal',
      });
    }
  }

  render() {
    const { attributes, setAttributes, isSelected } = this.props;

    let { className = '' } = this.props;

    const {
      tagName,
      text,
      icon,
      iconPosition,
      hideText,
      url,
      target,
      rel,
      size,
      color,
      textColor,
      borderRadius,
      borderWeight,
      borderColor,
      focusOutlineWeight,
      focusOutlineColor,
      hoverColor,
      hoverTextColor,
      hoverBorderColor,
    } = attributes;

    const sizes = [
      {
        label: 'XS',
        value: 'xs',
      },
      {
        label: 'S',
        value: 'sm',
      },
      {
        label: 'M',
        value: 'md',
      },
      {
        label: 'L',
        value: 'lg',
      },
      {
        label: 'XL',
        value: 'xl',
      },
    ];
    let isNormalState = false;
    let isHoveredState = false;
    let isFocusedState = false;

    if (isSelected) {
      isNormalState = true;

      if ('hover' === this.state.selectedColorState) {
        isNormalState = false;
        isHoveredState = true;
      } else if ('focus' === this.state.selectedColorState) {
        isNormalState = false;
        isFocusedState = true;
      }
    }

    className = classnames(
      'ghostkit-button',
      size ? `ghostkit-button-${size}` : '',
      hideText ? 'ghostkit-button-icon-only' : '',
      isNormalState ? 'ghostkit-button-is-normal-state' : '',
      isHoveredState ? 'ghostkit-button-is-hover-state' : '',
      isFocusedState ? 'ghostkit-button-is-focus-state' : '',
      className
    );

    // focus outline
    if (focusOutlineWeight && focusOutlineColor) {
      className = classnames(className, 'ghostkit-button-with-outline');
    }

    className = applyFilters('ghostkit.editor.className', className, this.props);

    const colorsTabs = [
      {
        name: 'normal',
        title: __('Normal', 'ghostkit'),
        className: 'ghostkit-control-tabs-tab',
      },
      {
        name: 'hover',
        title: __('Hover', 'ghostkit'),
        className: 'ghostkit-control-tabs-tab',
      },
    ];

    if (focusOutlineWeight && focusOutlineColor) {
      colorsTabs.push({
        name: 'focus',
        title: __('Focus', 'ghostkit'),
        className: 'ghostkit-control-tabs-tab',
      });
    }

    return (
      <Fragment>
        <InspectorControls>
          <PanelBody>
            <div className="blocks-size__main">
              <ToggleGroup
                value={size}
                options={sizes}
                onChange={(value) => {
                  setAttributes({ size: value });
                }}
              />
            </div>
          </PanelBody>
          <PanelBody>
            <RangeControl
              label={__('Corner Radius', 'ghostkit')}
              value={borderRadius}
              min="0"
              max="50"
              onChange={(value) => setAttributes({ borderRadius: value })}
            />
            <RangeControl
              label={__('Border Size', 'ghostkit')}
              value={borderWeight}
              min="0"
              max="6"
              onChange={(value) => setAttributes({ borderWeight: value })}
            />
            <RangeControl
              label={__('Focus Outline Size', 'ghostkit')}
              value={focusOutlineWeight}
              min="0"
              max="6"
              onChange={(value) => setAttributes({ focusOutlineWeight: value })}
            />
          </PanelBody>
          <PanelBody>
            <IconPicker
              label={__('Icon', 'ghostkit')}
              value={icon}
              onChange={(value) => setAttributes({ icon: value })}
            />
            {icon ? (
              <ToggleControl
                label={__('Show Icon Only', 'ghostkit')}
                checked={!!hideText}
                onChange={(val) => setAttributes({ hideText: val })}
              />
            ) : (
              ''
            )}
            {icon && !hideText ? (
              <SelectControl
                label={__('Icon Position', 'ghostkit')}
                value={iconPosition}
                options={[
                  {
                    value: 'left',
                    label: __('Left', 'ghostkit'),
                  },
                  {
                    value: 'right',
                    label: __('Right', 'ghostkit'),
                  },
                ]}
                onChange={(value) => setAttributes({ iconPosition: value })}
              />
            ) : (
              ''
            )}
          </PanelBody>
          <PanelBody
            title={
              <Fragment>
                {__('Colors', 'ghostkit')}
                <ColorIndicator colorValue={color} />
                <ColorIndicator colorValue={textColor} />
                {borderWeight ? <ColorIndicator colorValue={borderColor} /> : ''}
                {focusOutlineWeight && focusOutlineColor ? (
                  <ColorIndicator colorValue={focusOutlineColor} />
                ) : (
                  ''
                )}
              </Fragment>
            }
            initialOpen={false}
          >
            <TabPanel
              className="ghostkit-control-tabs ghostkit-control-tabs-wide"
              tabs={colorsTabs}
              onSelect={(state) => {
                this.setState({
                  selectedColorState: state,
                });
              }}
            >
              {(tabData) => {
                const isHover = 'hover' === tabData.name;

                // focus tab
                if ('focus' === tabData.name) {
                  return (
                    <ApplyFilters
                      name="ghostkit.editor.controls"
                      attribute="focusOutlineColor"
                      props={this.props}
                    >
                      <ColorPicker
                        label={__('Outline', 'ghostkit')}
                        value={focusOutlineColor}
                        onChange={(val) => setAttributes({ focusOutlineColor: val })}
                        alpha
                      />
                    </ApplyFilters>
                  );
                }

                return (
                  <Fragment>
                    <ApplyFilters
                      name="ghostkit.editor.controls"
                      attribute={isHover ? 'hoverColor' : 'color'}
                      props={this.props}
                    >
                      <ColorPicker
                        label={__('Background', 'ghostkit')}
                        value={isHover ? hoverColor : color}
                        onChange={(val) =>
                          setAttributes(isHover ? { hoverColor: val } : { color: val })
                        }
                        alpha
                      />
                    </ApplyFilters>
                    <ApplyFilters
                      name="ghostkit.editor.controls"
                      attribute={isHover ? 'hoverTextColor' : 'textColor'}
                      props={this.props}
                    >
                      <ColorPicker
                        label={__('Text', 'ghostkit')}
                        value={isHover ? hoverTextColor : textColor}
                        onChange={(val) =>
                          setAttributes(isHover ? { hoverTextColor: val } : { textColor: val })
                        }
                        alpha
                      />
                    </ApplyFilters>
                    {borderWeight ? (
                      <ApplyFilters
                        name="ghostkit.editor.controls"
                        attribute={isHover ? 'hoverBorderColor' : 'borderColor'}
                        props={this.props}
                      >
                        <ColorPicker
                          label={__('Border', 'ghostkit')}
                          value={isHover ? hoverBorderColor : borderColor}
                          onChange={(val) =>
                            setAttributes(
                              isHover ? { hoverBorderColor: val } : { borderColor: val }
                            )
                          }
                          alpha
                        />
                      </ApplyFilters>
                    ) : (
                      ''
                    )}
                  </Fragment>
                );
              }}
            </TabPanel>
          </PanelBody>
        </InspectorControls>
        <div className={className}>
          {icon ? (
            <div
              className={`ghostkit-button-icon ghostkit-button-icon-${
                'right' === iconPosition ? 'right' : 'left'
              }`}
            >
              <IconPicker.Dropdown
                onChange={(value) => setAttributes({ icon: value })}
                value={icon}
                renderToggle={({ onToggle }) => (
                  <IconPicker.Preview onClick={onToggle} name={icon} />
                )}
              />
            </div>
          ) : (
            ''
          )}
          {!hideText ? (
            <RichText
              placeholder={__('Write text…', 'ghostkit')}
              value={text}
              onChange={(value) => setAttributes({ text: value })}
              isSelected={isSelected}
              withoutInteractiveFormatting
              keepPlaceholderOnFocus
            />
          ) : (
            ''
          )}
        </div>
        {!tagName || 'a' === tagName ? (
          <URLPicker
            url={url}
            rel={rel}
            target={target}
            onChange={(data) => {
              setAttributes(data);
            }}
            isSelected={isSelected}
            toolbarSettings
            inspectorSettings
          />
        ) : (
          ''
        )}
      </Fragment>
    );
  }
}

export default BlockEdit;

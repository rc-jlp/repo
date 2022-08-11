/**
 * External dependencies
 */
import classnames from 'classnames/dedupe';

/**
 * Internal dependencies
 */
import ColorPicker from '../../components/color-picker';
import IconPicker from '../../components/icon-picker';
import URLPicker from '../../components/url-picker';
import ColorIndicator from '../../components/color-indicator';
import ApplyFilters from '../../components/apply-filters';

/**
 * WordPress dependencies
 */
const { applyFilters } = wp.hooks;

const { __ } = wp.i18n;

const { Component, Fragment } = wp.element;

const { withSelect } = wp.data;

const {
  BaseControl,
  PanelBody,
  RangeControl,
  ToggleControl,
  TabPanel,
  Toolbar,
  ToolbarGroup,
  ToolbarButton,
} = wp.components;

const { InspectorControls, InnerBlocks, BlockControls } = wp.blockEditor;

/**
 * Block Edit Class.
 */
class BlockEdit extends Component {
  render() {
    const { attributes, setAttributes, isSelected, hasChildBlocks } = this.props;

    let { className = '' } = this.props;

    const {
      icon,
      iconPosition,
      iconSize,
      showContent,
      iconColor,
      hoverIconColor,
      url,
      target,
      rel,
    } = attributes;

    className = classnames('ghostkit-icon-box', className);

    className = applyFilters('ghostkit.editor.className', className, this.props);

    return (
      <Fragment>
        <InspectorControls>
          <PanelBody>
            <IconPicker
              label={__('Icon', 'ghostkit')}
              value={icon}
              onChange={(value) => setAttributes({ icon: value })}
            />
            {icon ? (
              <Fragment>
                <RangeControl
                  label={__('Icon Size', 'ghostkit')}
                  value={iconSize}
                  onChange={(value) => setAttributes({ iconSize: value })}
                  min={20}
                  max={100}
                  beforeIcon="editor-textcolor"
                  afterIcon="editor-textcolor"
                />
                <BaseControl label={__('Icon Position', 'ghostkit')}>
                  <div>
                    <Toolbar label={__('Icon Position', 'ghostkit')}>
                      <ToolbarButton
                        icon="align-center"
                        title={__('Top', 'ghostkit')}
                        onClick={() => setAttributes({ iconPosition: 'top' })}
                        isActive={'top' === iconPosition}
                      />
                      <ToolbarButton
                        icon="align-left"
                        title={__('Left', 'ghostkit')}
                        onClick={() => setAttributes({ iconPosition: 'left' })}
                        isActive={'left' === iconPosition}
                      />
                      <ToolbarButton
                        icon="align-right"
                        title={__('Right', 'ghostkit')}
                        onClick={() => setAttributes({ iconPosition: 'right' })}
                        isActive={'right' === iconPosition}
                      />
                    </Toolbar>
                  </div>
                </BaseControl>
              </Fragment>
            ) : (
              ''
            )}
            {!showContent || icon ? (
              <ToggleControl
                label={__('Show Content', 'ghostkit')}
                checked={!!showContent}
                onChange={(val) => setAttributes({ showContent: val })}
              />
            ) : (
              ''
            )}
          </PanelBody>
          <PanelBody
            title={
              <Fragment>
                {__('Colors', 'ghostkit')}
                <ColorIndicator colorValue={iconColor} />
              </Fragment>
            }
            initialOpen={false}
          >
            <TabPanel
              className="ghostkit-control-tabs ghostkit-control-tabs-wide"
              tabs={[
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
              ]}
            >
              {(tabData) => {
                const isHover = 'hover' === tabData.name;
                return (
                  <ApplyFilters
                    name="ghostkit.editor.controls"
                    attribute={isHover ? 'hoverIconColor' : 'iconColor'}
                    props={this.props}
                  >
                    <ColorPicker
                      label={__('Icon', 'ghostkit')}
                      value={isHover ? hoverIconColor : iconColor}
                      onChange={(val) =>
                        setAttributes(isHover ? { hoverIconColor: val } : { iconColor: val })
                      }
                      alpha
                    />
                  </ApplyFilters>
                );
              }}
            </TabPanel>
          </PanelBody>
        </InspectorControls>
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
        {icon ? (
          <BlockControls>
            <ToolbarGroup>
              <ToolbarButton
                icon="align-center"
                title={__('Top', 'ghostkit')}
                onClick={() => setAttributes({ iconPosition: 'top' })}
                isActive={'top' === iconPosition}
              />
              <ToolbarButton
                icon="align-left"
                title={__('Left', 'ghostkit')}
                onClick={() => setAttributes({ iconPosition: 'left' })}
                isActive={'left' === iconPosition}
              />
              <ToolbarButton
                icon="align-right"
                title={__('Right', 'ghostkit')}
                onClick={() => setAttributes({ iconPosition: 'right' })}
                isActive={'right' === iconPosition}
              />
            </ToolbarGroup>
          </BlockControls>
        ) : (
          ''
        )}
        <div className={className}>
          {icon ? (
            <div
              className={`ghostkit-icon-box-icon ghostkit-icon-box-icon-align-${
                iconPosition || 'left'
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
          {showContent ? (
            <div className="ghostkit-icon-box-content">
              <InnerBlocks
                templateLock={false}
                renderAppender={
                  hasChildBlocks ? undefined : () => <InnerBlocks.ButtonBlockAppender />
                }
              />
            </div>
          ) : (
            ''
          )}
        </div>
      </Fragment>
    );
  }
}

export default withSelect((select, props) => {
  const { clientId } = props;
  const blockEditor = select('core/block-editor');

  return {
    hasChildBlocks: blockEditor ? 0 < blockEditor.getBlockOrder(clientId).length : false,
  };
})(BlockEdit);

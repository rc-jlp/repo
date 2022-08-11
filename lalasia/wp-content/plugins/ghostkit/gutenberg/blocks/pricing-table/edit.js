/**
 * External dependencies
 */
import classnames from 'classnames/dedupe';

/**
 * Internal dependencies
 */
import GapSettings from '../../components/gap-settings';
import getIcon from '../../utils/get-icon';

/**
 * WordPress dependencies
 */
const { applyFilters } = wp.hooks;

const { __ } = wp.i18n;

const { Component, Fragment } = wp.element;

const { BaseControl, Button, PanelBody, Toolbar, ToolbarGroup, ToolbarButton } = wp.components;

const { InspectorControls, InnerBlocks, BlockControls, AlignmentToolbar } = wp.blockEditor;

const { createBlock } = wp.blocks;

const { compose } = wp.compose;

const { withSelect, withDispatch } = wp.data;

/**
 * Block Edit Class.
 */
class BlockEdit extends Component {
  constructor(props) {
    super(props);

    this.getInnerBlocksTemplate = this.getInnerBlocksTemplate.bind(this);
    this.maybeUpdateColumnsNumber = this.maybeUpdateColumnsNumber.bind(this);
  }

  componentDidMount() {
    this.maybeUpdateColumnsNumber();
  }

  componentDidUpdate() {
    this.maybeUpdateColumnsNumber();
  }

  /**
   * Returns the layouts configuration for a given number of items.
   *
   * @return {Object[]} Items layout configuration.
   */
  getInnerBlocksTemplate() {
    const { attributes } = this.props;

    const { count } = attributes;

    const result = [];

    if (0 < count) {
      for (let k = 1; k <= count; k += 1) {
        result.push(['ghostkit/pricing-table-item']);
      }
    }

    return result;
  }

  /**
   * Update current columns number.
   */
  maybeUpdateColumnsNumber() {
    const { count } = this.props.attributes;

    const { block, setAttributes } = this.props;

    if (block && block.innerBlocks && count !== block.innerBlocks.length) {
      setAttributes({
        count: block.innerBlocks.length,
      });
    }
  }

  render() {
    const { attributes, setAttributes, isSelectedBlockInRoot, insertPricingItem } = this.props;

    let { className = '' } = this.props;

    const { count, gap, gapCustom, verticalAlign, horizontalAlign } = attributes;

    className = classnames(
      className,
      'ghostkit-pricing-table',
      `ghostkit-pricing-table-gap-${gap}`,
      count ? `ghostkit-pricing-table-items-${count}` : false,
      verticalAlign ? `ghostkit-pricing-table-align-vertical-${verticalAlign}` : false,
      horizontalAlign ? `ghostkit-pricing-table-align-horizontal-${horizontalAlign}` : false
    );

    className = applyFilters('ghostkit.editor.className', className, this.props);

    return (
      <Fragment>
        <BlockControls>
          <AlignmentToolbar
            value={horizontalAlign}
            onChange={(val) => setAttributes({ horizontalAlign: val })}
          />
        </BlockControls>
        {1 < count ? (
          <BlockControls>
            <ToolbarGroup>
              <ToolbarButton
                icon={getIcon('icon-vertical-top')}
                title={__('ItemsVertical Start', 'ghostkit')}
                onClick={() => setAttributes({ verticalAlign: '' })}
                isActive={'' === verticalAlign}
              />
              <ToolbarButton
                icon={getIcon('icon-vertical-center')}
                title={__('ItemsVertical Center', 'ghostkit')}
                onClick={() => setAttributes({ verticalAlign: 'center' })}
                isActive={'center' === verticalAlign}
              />
              <ToolbarButton
                icon={getIcon('icon-vertical-bottom')}
                title={__('ItemsVertical End', 'ghostkit')}
                onClick={() => setAttributes({ verticalAlign: 'end' })}
                isActive={'end' === verticalAlign}
              />
            </ToolbarGroup>
          </BlockControls>
        ) : (
          ''
        )}
        <InspectorControls>
          <PanelBody>
            <BaseControl label={__('Vertical align', 'ghostkit')}>
              <div>
                <Toolbar label={__('Vertical align', 'ghostkit')}>
                  <ToolbarButton
                    icon={getIcon('icon-vertical-top')}
                    title={__('ItemsVertical Start', 'ghostkit')}
                    onClick={() => setAttributes({ verticalAlign: '' })}
                    isActive={'' === verticalAlign}
                  />
                  <ToolbarButton
                    icon={getIcon('icon-vertical-center')}
                    title={__('ItemsVertical Center', 'ghostkit')}
                    onClick={() => setAttributes({ verticalAlign: 'center' })}
                    isActive={'center' === verticalAlign}
                  />
                  <ToolbarButton
                    icon={getIcon('icon-vertical-bottom')}
                    title={__('ItemsVertical End', 'ghostkit')}
                    onClick={() => setAttributes({ verticalAlign: 'end' })}
                    isActive={'end' === verticalAlign}
                  />
                </Toolbar>
              </div>
            </BaseControl>
            <BaseControl label={__('Horizontal align', 'ghostkit')}>
              <div>
                <AlignmentToolbar
                  value={horizontalAlign}
                  onChange={(val) => setAttributes({ horizontalAlign: val })}
                  isCollapsed={false}
                />
              </div>
            </BaseControl>
          </PanelBody>
          <PanelBody>
            <GapSettings
              gap={gap}
              gapCustom={gapCustom}
              onChange={(data) => {
                setAttributes(data);
              }}
            />
          </PanelBody>
        </InspectorControls>
        <div className={className}>
          {0 < count ? (
            <InnerBlocks
              template={this.getInnerBlocksTemplate()}
              allowedBlocks={['ghostkit/pricing-table-item']}
              orientation="horizontal"
              renderAppender={false}
            />
          ) : (
            ''
          )}
        </div>
        {isSelectedBlockInRoot && 6 > count ? (
          <div className="ghostkit-pricing-table-add-item">
            <Button
              isSecondary
              icon={
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 24 24"
                  width="24"
                  height="24"
                  role="img"
                  ariaHidden="true"
                  focusable="false"
                >
                  <path d="M18 11.2h-5.2V6h-1.6v5.2H6v1.6h5.2V18h1.6v-5.2H18z" />
                </svg>
              }
              onClick={() => {
                insertPricingItem();
              }}
            >
              {__('Add Pricing Table', 'ghostkit')}
            </Button>
          </div>
        ) : (
          ''
        )}
      </Fragment>
    );
  }
}

export default compose([
  withSelect((select, ownProps) => {
    const { getBlock, isBlockSelected, hasSelectedInnerBlock } = select('core/block-editor');

    const { clientId } = ownProps;

    return {
      block: getBlock(clientId),
      isSelectedBlockInRoot: isBlockSelected(clientId) || hasSelectedInnerBlock(clientId, true),
    };
  }),
  withDispatch((dispatch, ownProps) => {
    const { insertBlock } = dispatch('core/block-editor');

    const { clientId } = ownProps;

    return {
      insertPricingItem() {
        insertBlock(createBlock('ghostkit/pricing-table-item'), undefined, clientId);
      },
    };
  }),
])(BlockEdit);

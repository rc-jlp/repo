/**
 * External dependencies
 */
import classnames from 'classnames/dedupe';

/**
 * Internal dependencies
 */
import getIcon from '../../utils/get-icon';
import ApplyFilters from '../../components/apply-filters';
import GapSettings from '../../components/gap-settings';
import ToggleGroup from '../../components/toggle-group';
import { TemplatesModal } from '../../plugins/templates';

/**
 * WordPress dependencies
 */
const { applyFilters } = wp.hooks;

const { __ } = wp.i18n;

const { Fragment, useState } = wp.element;

const { Button, PanelBody, RangeControl, Placeholder, ToolbarGroup, ToolbarButton, Tooltip } =
  wp.components;

const {
  InspectorControls,
  BlockControls,
  useBlockProps,
  useInnerBlocksProps: __stableUseInnerBlocksProps,
  __experimentalUseInnerBlocksProps,
} = wp.blockEditor;

const { useSelect, useDispatch } = wp.data;

const { createBlock } = wp.blocks;

const useInnerBlocksProps = __stableUseInnerBlocksProps || __experimentalUseInnerBlocksProps;

/**
 * Block Edit Class.
 */
export default function BlockEdit(props) {
  const { clientId, attributes, setAttributes, isSelected } = props;

  const { gap, gapCustom, verticalAlign, horizontalAlign } = attributes;

  const [isTemplatesModalOpen, setIsTemplatesModalOpen] = useState(false);

  const { removeBlock, replaceInnerBlocks } = useDispatch('core/block-editor');

  const { getBlocks, columnsCount } = useSelect((select) => {
    const { getBlockCount } = select('core/block-editor');

    return {
      getBlocks: select('core/block-editor').getBlocks,
      columnsCount: getBlockCount(clientId),
    };
  }, []);

  /**
   * Get columns sizes array from layout string
   *
   * @param {string} layout - layout data. Example: `3-6-3`
   *
   * @return {array}.
   */
  function getColumnsFromLayout(layout) {
    const result = [];
    const columnsData = layout.split('-');

    columnsData.forEach((col) => {
      const colAttrs = {
        size: col,
      };

      if ('a' === col) {
        colAttrs.size = 'auto';
      } else if ('g' === col) {
        colAttrs.size = 'grow';
      }

      // responsive.
      if (2 === columnsData.length) {
        colAttrs.md_size = '12';
      }
      if (3 === columnsData.length) {
        colAttrs.lg_size = '12';
      }
      if (4 === columnsData.length) {
        colAttrs.md_size = '12';
        colAttrs.lg_size = '6';
      }
      if (5 === columnsData.length) {
        colAttrs.sm_size = '12';
        colAttrs.md_size = '5';
        colAttrs.lg_size = '4';
      }
      if (6 === columnsData.length) {
        colAttrs.sm_size = '6';
        colAttrs.md_size = '4';
        colAttrs.lg_size = '3';
      }

      result.push(colAttrs);
    });

    return result;
  }

  /**
   * Select predefined layout.
   *
   * @param {String} layout layout string.
   */
  function onLayoutSelect(layout) {
    const columnsData = getColumnsFromLayout(layout);
    const newInnerBlocks = [];

    columnsData.forEach((colAttrs) => {
      newInnerBlocks.push(createBlock('ghostkit/grid-column', colAttrs));
    });

    replaceInnerBlocks(clientId, newInnerBlocks, false);
  }

  /**
   * Layouts selector when no columns selected.
   *
   * @return {jsx}.
   */
  function getLayoutsSelector() {
    let layouts = [
      '12',
      '6-6',
      '4-4-4',
      '3-3-3-3',

      '5-7',
      '7-5',
      '3-3-6',
      '3-6-3',

      '6-3-3',
      '2-8-2',
      'g-g-g-g-g',
      '2-2-2-2-2-2',
    ];
    layouts = applyFilters('ghostkit.editor.grid.layouts', layouts, props);

    return (
      <Placeholder
        icon={getIcon('block-grid')}
        label={__('Grid', 'ghostkit')}
        instructions={__('Select one layout to get started.', 'ghostkit')}
        className="ghostkit-select-layout"
      >
        <div className="ghostkit-grid-layout-preview">
          {layouts.map((layout) => {
            const columnsData = getColumnsFromLayout(layout);

            return (
              <Button
                key={`layout-${layout}`}
                className="ghostkit-grid-layout-preview-btn ghostkit-grid"
                onClick={() => onLayoutSelect(layout)}
              >
                {columnsData.map((colAttrs, i) => {
                  const colName = `layout-${layout}-col-${i}`;

                  return (
                    <div
                      key={colName}
                      className={classnames('ghostkit-col', `ghostkit-col-${colAttrs.size}`)}
                    />
                  );
                })}
              </Button>
            );
          })}
        </div>
        <Button
          isPrimary
          onClick={() => {
            setIsTemplatesModalOpen(true);
          }}
        >
          {__('Select Template', 'ghostkit')}
        </Button>
        {isTemplatesModalOpen || props.attributes.isTemplatesModalOnly ? (
          <TemplatesModal
            replaceBlockId={clientId}
            onRequestClose={() => {
              setIsTemplatesModalOpen(false);

              if (props.attributes.isTemplatesModalOnly) {
                removeBlock(clientId);
              }
            }}
          />
        ) : (
          ''
        )}
      </Placeholder>
    );
  }

  /**
   * Updates the column count
   *
   * @param {number} newColumns New column count.
   */
  function updateColumns(newColumns) {
    // Remove Grid block.
    if (1 > newColumns) {
      removeBlock(clientId);

      // Add new columns.
    } else if (newColumns > columnsCount) {
      const newCount = newColumns - columnsCount;
      const newInnerBlocks = [...getBlocks(clientId)];

      for (let i = 1; i <= newCount; i += 1) {
        newInnerBlocks.push(createBlock('ghostkit/grid-column', { size: 3 }));
      }

      replaceInnerBlocks(clientId, newInnerBlocks, false);

      // Remove columns.
    } else if (newColumns < columnsCount) {
      const newInnerBlocks = [...getBlocks(clientId)];
      newInnerBlocks.splice(newColumns, columnsCount - newColumns);

      replaceInnerBlocks(clientId, newInnerBlocks, false);
    }
  }

  let className = classnames(
    'ghostkit-grid',
    `ghostkit-grid-gap-${gap}`,
    verticalAlign ? `ghostkit-grid-align-items-${verticalAlign}` : false,
    horizontalAlign ? `ghostkit-grid-justify-content-${horizontalAlign}` : false
  );

  // background
  const background = applyFilters('ghostkit.editor.grid.background', '', props);

  if (background) {
    className = classnames(className, 'ghostkit-grid-with-bg');
  }

  className = applyFilters('ghostkit.editor.className', className, props);

  const blockProps = useBlockProps({
    className,
  });

  const { children, ...innerBlocksProps } = useInnerBlocksProps(blockProps, {
    allowedBlocks: ['ghostkit/grid-column'],
    orientation: 'horizontal',
    renderAppender: false,
  });

  return (
    <div {...innerBlocksProps}>
      {0 < columnsCount ? (
        <BlockControls>
          <ToolbarGroup>
            <ToolbarButton
              icon={getIcon('icon-vertical-top')}
              title={__('Content Vertical Start', 'ghostkit')}
              onClick={() => setAttributes({ verticalAlign: '' })}
              isActive={'' === verticalAlign}
            />
            <ToolbarButton
              icon={getIcon('icon-vertical-center')}
              title={__('Content Vertical Center', 'ghostkit')}
              onClick={() => setAttributes({ verticalAlign: 'center' })}
              isActive={'center' === verticalAlign}
            />
            <ToolbarButton
              icon={getIcon('icon-vertical-bottom')}
              title={__('Content Vertical End', 'ghostkit')}
              onClick={() => setAttributes({ verticalAlign: 'end' })}
              isActive={'end' === verticalAlign}
            />
          </ToolbarGroup>
        </BlockControls>
      ) : (
        ''
      )}
      <InspectorControls>
        <ApplyFilters name="ghostkit.editor.controls" attribute="columns" props={props}>
          <PanelBody>
            <RangeControl
              label={__('Columns', 'ghostkit')}
              value={columnsCount}
              onChange={(value) => updateColumns(value)}
              min={1}
              max={12}
            />
          </PanelBody>
        </ApplyFilters>
      </InspectorControls>
      {0 < columnsCount ? (
        <InspectorControls>
          <PanelBody>
            <ToggleGroup
              label={__('Vertical alignment', 'ghostkit')}
              value={verticalAlign}
              options={[
                {
                  label: getIcon('icon-vertical-top'),
                  value: '',
                },
                {
                  label: getIcon('icon-vertical-center'),
                  value: 'center',
                },
                {
                  label: getIcon('icon-vertical-bottom'),
                  value: 'end',
                },
              ]}
              onChange={(value) => {
                setAttributes({ verticalAlign: value });
              }}
            />
            <ToggleGroup
              label={__('Horizontal alignment', 'ghostkit')}
              value={horizontalAlign}
              options={[
                {
                  label: getIcon('icon-horizontal-start'),
                  value: '',
                },
                {
                  label: getIcon('icon-horizontal-center'),
                  value: 'center',
                },
                {
                  label: getIcon('icon-horizontal-end'),
                  value: 'end',
                },
                {
                  label: getIcon('icon-horizontal-around'),
                  value: 'around',
                },
                {
                  label: getIcon('icon-horizontal-between'),
                  value: 'between',
                },
              ]}
              onChange={(value) => {
                setAttributes({ horizontalAlign: value });
              }}
            />
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
      ) : (
        ''
      )}
      <InspectorControls>
        <div className="ghostkit-background-controls">
          <ApplyFilters name="ghostkit.editor.controls" attribute="background" props={props} />
        </div>
      </InspectorControls>
      {0 < columnsCount ? (
        <Fragment>
          {background}
          {!isSelected ? (
            <div className="ghostkit-grid-button-select">
              <Tooltip text={__('Select Grid', 'ghostkit')}>{getIcon('block-grid')}</Tooltip>
            </div>
          ) : (
            ''
          )}
          <div className="ghostkit-grid-inner">{children}</div>
        </Fragment>
      ) : (
        getLayoutsSelector()
      )}
    </div>
  );
}

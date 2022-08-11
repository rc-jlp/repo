/* eslint-disable react/no-danger */
/**
 * Internal dependencies
 */
import ColorPicker from '../../components/color-picker';
import FocalPointPicker from '../../components/focal-point-picker';
import ToggleGroup from '../../components/toggle-group';
import ColorIndicator from '../../components/color-indicator';
import dashCaseToTitle from '../../utils/dash-case-to-title';
import { maybeEncode, maybeDecode } from '../../utils/encode-decode';

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

const { Component, Fragment } = wp.element;

const { addFilter } = wp.hooks;

const { MediaUpload } = wp.blockEditor;

const { hasBlockSupport } = wp.blocks;

const { PanelBody, Button, SelectControl, ExternalLink } = wp.components;

const { withSelect } = wp.data;

/**
 * Filters registered block settings, extending attributes to include backgrounds.
 *
 * @param  {Object} blockSettings Original block settings
 * @return {Object}               Filtered block settings
 */
export function addAttribute(blockSettings) {
  if ('ghostkit/grid' === blockSettings.name || 'ghostkit/grid-column' === blockSettings.name) {
    blockSettings.supports.awb = true;
  }

  let allow = false;

  if (hasBlockSupport(blockSettings, 'awb', false)) {
    allow = true;
  }

  if (allow) {
    blockSettings.attributes.awb_type = {
      type: 'string',
      default: 'color',
    };
    blockSettings.attributes.awb_align = {
      type: 'string',
    };
    blockSettings.attributes.awb_color = {
      type: 'string',
      default: '',
    };
    blockSettings.attributes.awb_image = {
      type: 'number',
      default: '',
    };
    blockSettings.attributes.awb_imageTag = {
      type: 'string',
      default: '',
    };
    blockSettings.attributes.awb_imageSizes = {
      type: 'object',
      default: '',
    };
    blockSettings.attributes.awb_imageSize = {
      type: 'string',
      default: 'full',
    };
    blockSettings.attributes.awb_imageBackgroundSize = {
      type: 'string',
      default: 'cover',
    };
    blockSettings.attributes.awb_imageBackgroundPosition = {
      type: 'string',
      default: '50% 50%',
    };
  }

  return blockSettings;
}

/**
 * Select image
 *
 * @param {Object} media - media data.
 * @param {Function} setAttributes - function to set attributes on the block.
 */
function onImageSelect(media, setAttributes) {
  setAttributes({
    image: '',
    imageSizes: '',
  });

  wp.media
    .attachment(media.id)
    .fetch()
    .then((data) => {
      if (data && data.sizes) {
        const { url } =
          data.sizes['post-thumbnail'] ||
          data.sizes.medium ||
          data.sizes.medium_large ||
          data.sizes.full;
        if (url) {
          setAttributes({
            image: media.id,
            imageSizes: data.sizes,
          });
        }
      }
    });
}

class BackgroundControlsInspector extends Component {
  constructor(props) {
    super(props);

    this.updateAwbAttributes = this.updateAwbAttributes.bind(this);
    this.onUpdate = this.onUpdate.bind(this);
  }

  componentDidMount() {
    this.onUpdate();
  }

  componentDidUpdate() {
    this.onUpdate();
  }

  onUpdate() {
    const { fetchImageTag, attributes } = this.props;

    const { awb_imageTag: imageTag } = attributes;

    // set image tag to attribute
    if (fetchImageTag && maybeEncode(fetchImageTag) !== imageTag) {
      this.updateAwbAttributes({ imageTag: maybeEncode(fetchImageTag) });
    }
  }

  updateAwbAttributes(attr) {
    const { setAttributes } = this.props;

    const newAttrs = {};

    Object.keys(attr).forEach((k) => {
      newAttrs[`awb_${k}`] = attr[k];
    });

    setAttributes(newAttrs);
  }

  render() {
    const { attributes } = this.props;

    const setAttributes = this.updateAwbAttributes;

    const {
      awb_color: color,
      awb_type: type,
      awb_image: image,
      awb_imageTag: imageTag,
      awb_imageSizes: imageSizes,
      awb_imageSize: imageSize,
      awb_imageBackgroundSize: imageBackgroundSize,
      awb_imageBackgroundPosition: imageBackgroundPosition,
    } = attributes;

    return (
      <PanelBody title={__('Background', 'ghostkit')} initialOpen={false}>
        <ToggleGroup
          value={'video' === type || 'yt_vm_video' === type ? 'yt_vm_video' : type}
          options={[
            {
              label: __('Color', 'ghostkit'),
              value: 'color',
            },
            {
              label: __('Image', 'ghostkit'),
              value: 'image',
            },
            {
              label: __('Video', 'ghostkit'),
              value: 'yt_vm_video',
            },
          ]}
          onChange={(value) => {
            setAttributes({ type: value });
          }}
        />

        {'image' === type ? (
          <PanelBody title={__('Image', 'ghostkit')} initialOpen={'image' === type}>
            {/* Select Image */}
            {!image || !imageTag ? (
              <MediaUpload
                onSelect={(media) => {
                  onImageSelect(media, setAttributes);
                }}
                allowedTypes={['image']}
                value={image}
                render={({ open }) => (
                  <Button onClick={open} isPrimary>
                    {__('Select image', 'ghostkit')}
                  </Button>
                )}
              />
            ) : (
              ''
            )}

            {image && imageTag ? (
              <Fragment>
                <FocalPointPicker
                  value={imageBackgroundPosition}
                  image={maybeDecode(imageTag)}
                  onChange={(v) => setAttributes({ imageBackgroundPosition: v })}
                />
                {imageSizes ? (
                  <SelectControl
                    label={__('Size', 'ghostkit')}
                    value={imageSize}
                    options={(() => {
                      const result = [];
                      Object.keys(imageSizes).forEach((k) => {
                        result.push({
                          value: k,
                          label: dashCaseToTitle(k),
                        });
                      });
                      return result;
                    })()}
                    onChange={(v) => setAttributes({ imageSize: v })}
                  />
                ) : (
                  ''
                )}
                <SelectControl
                  label={__('Background size', 'ghostkit')}
                  value={imageBackgroundSize}
                  options={[
                    {
                      label: __('Cover', 'ghostkit'),
                      value: 'cover',
                    },
                    {
                      label: __('Contain', 'ghostkit'),
                      value: 'contain',
                    },
                    {
                      label: __('Pattern', 'ghostkit'),
                      value: 'pattern',
                    },
                  ]}
                  onChange={(v) => setAttributes({ imageBackgroundSize: v })}
                />
                <Button
                  isLink
                  onClick={(e) => {
                    setAttributes({
                      image: '',
                      imageTag: '',
                      imageSizes: '',
                    });
                    e.preventDefault();
                  }}
                >
                  {__('Remove image', 'ghostkit')}
                </Button>
              </Fragment>
            ) : (
              ''
            )}
          </PanelBody>
        ) : (
          ''
        )}

        {'color' === type ? (
          <ColorPicker
            label={__('Background Color', 'ghostkit')}
            value={color}
            onChange={(val) => setAttributes({ color: val })}
            alpha
          />
        ) : (
          <PanelBody
            title={
              <Fragment>
                {__('Overlay', 'ghostkit')}
                <ColorIndicator colorValue={color} />
              </Fragment>
            }
            initialOpen={'color' === type}
          >
            <ColorPicker
              label={__('Background Color', 'ghostkit')}
              value={color}
              onChange={(val) => setAttributes({ color: val })}
              alpha
            />
          </PanelBody>
        )}

        <p>
          {__(
            'Install AWB plugin to set video backgrounds and images with parallax support.',
            'ghostkit'
          )}
        </p>
        <ExternalLink
          className="components-button is-button is-secondary is-small"
          href="https://wordpress.org/plugins/advanced-backgrounds/"
        >
          {__('Install', 'ghostkit')}
        </ExternalLink>
      </PanelBody>
    );
  }
}

const BackgroundControlsInspectorWithSelect = withSelect((select, props) => {
  const {
    awb_image: image,
    awb_imageSize: imageSize,
    awb_imageBackgroundSize: imageBackgroundSize,
  } = props.attributes;

  if (!image) {
    return false;
  }

  const data = {
    id: image,
    size: imageSize,
    attr: {
      class: 'jarallax-img',
    },
  };

  // background image with pattern size
  if ('pattern' === imageBackgroundSize) {
    data.div_tag = true;
  }

  return {
    fetchImageTag: select('ghostkit/base/images').getImageTagData(data),
  };
})(BackgroundControlsInspector);

/**
 * Override background control to add AWB settings
 *
 * @param {Object} Control JSX control.
 * @param {Object} props additional props.
 *
 * @return {Object} Control.
 */
function addBackgroundControls(Control, props) {
  if ('background' === props.attribute && hasBlockSupport(props.props.name, 'awb', false)) {
    return <BackgroundControlsInspectorWithSelect {...props.props} />;
  }

  return Control;
}

/**
 * Override the default edit UI to include background preview.
 *
 * @param {Object} background background JSX.
 * @param {Object} props additional props.
 *
 * @return {Object} Control.
 */
function addEditorBackground(background, props) {
  if (hasBlockSupport(props.name, 'awb', false)) {
    const { awb_color: color, awb_type: type, awb_imageTag: imageTag } = props.attributes;

    let addBackground = false;

    if ('color' === type && color) {
      addBackground = true;
    }

    if ('image' === type && (color || imageTag)) {
      addBackground = true;
    }

    if (addBackground) {
      return (
        <div className="awb-gutenberg-preview-block">
          {color ? <div className="nk-awb-overlay" style={{ 'background-color': color }} /> : ''}
          {'image' === type && imageTag ? (
            <div
              className="nk-awb-inner"
              dangerouslySetInnerHTML={{ __html: maybeDecode(imageTag) }}
            />
          ) : (
            ''
          )}
        </div>
      );
    }

    return null;
  }

  return background;
}

/**
 * Add background
 *
 * @param {Object} background Background jsx.
 * @param {Object} props  Block properties.
 *
 * @return {Object} Filtered props applied to save element.
 */
function addSaveBackground(background, props) {
  if (hasBlockSupport(props.name, 'awb', false)) {
    const {
      awb_color: color,
      awb_type: type,
      awb_imageBackgroundSize: imageBackgroundSize,
      awb_imageBackgroundPosition: imageBackgroundPosition,
    } = props.attributes;

    let { awb_imageTag: imageTag } = props.attributes;

    let addBackground = false;

    if ('color' === type && color) {
      addBackground = true;
    }

    if ('image' === type && (color || imageTag)) {
      addBackground = true;
    }

    if (addBackground) {
      const dataAttrs = {
        'data-awb-type': type,
      };

      if ('image' === type) {
        if (imageBackgroundSize) {
          dataAttrs['data-awb-image-background-size'] = imageBackgroundSize;
        }
        if (imageBackgroundPosition) {
          dataAttrs['data-awb-image-background-position'] = imageBackgroundPosition;
        }
      }

      // Fix style tag background.
      if ('image' === type && imageTag) {
        imageTag = maybeDecode(imageTag);

        imageTag = imageTag.replace('url(&quot;', "url('");
        imageTag = imageTag.replace('&quot;);', "');");
      }

      return (
        <div className="nk-awb">
          <div className="nk-awb-wrap" {...dataAttrs}>
            {color ? <div className="nk-awb-overlay" style={{ 'background-color': color }} /> : ''}
            {'image' === type && imageTag ? (
              <div className="nk-awb-inner" dangerouslySetInnerHTML={{ __html: imageTag }} />
            ) : (
              ''
            )}
          </div>
        </div>
      );
    }

    return null;
  }

  return background;
}

addFilter('blocks.registerBlockType', 'ghostkit/grid/awb/additional-attributes', addAttribute);
addFilter(
  'ghostkit.editor.controls',
  'ghostkit/grid/awb/addBackgroundControls',
  addBackgroundControls
);
addFilter(
  'ghostkit.editor.grid.background',
  'ghostkit/grid/awb/addEditorBackground',
  addEditorBackground
);
addFilter(
  'ghostkit.editor.grid-column.background',
  'ghostkit/grid-column/awb/addEditorBackground',
  addEditorBackground
);
addFilter('ghostkit.blocks.grid.background', 'ghostkit/grid/addSaveBackground', addSaveBackground);
addFilter(
  'ghostkit.blocks.grid-column.background',
  'ghostkit/grid-column/addSaveBackground',
  addSaveBackground
);

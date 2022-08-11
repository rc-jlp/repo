/**
 * External dependencies
 */
import classnames from 'classnames/dedupe';
import { GiphyFetch } from '@giphy/js-fetch-api';

/**
 * Internal dependencies
 */
import getIcon from '../../utils/get-icon';

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

const { Component, Fragment, createRef } = wp.element;

const { Button, PanelBody, Placeholder, Spinner, TextareaControl, ExternalLink } = wp.components;

const { InspectorControls, RichText } = wp.blockEditor;

const GIPHY_API_KEY = 'Qm6AupESs2abqy7fSeHNQ892EWSS28r8';

// use @giphy/js-fetch-api to fetch gifs, instantiate with your api key
const gf = new GiphyFetch(GIPHY_API_KEY);

/**
 * Block Edit Class.
 */
export default class BlockEdit extends Component {
  constructor(props) {
    super(props);

    this.textControlRef = createRef();

    this.state = {
      captionFocus: false,
      results: null,
      loading: false,
    };

    this.onFormSubmit = this.onFormSubmit.bind(this);
    this.onSubmit = this.onSubmit.bind(this);
    this.setFocus = this.setFocus.bind(this);
    this.splitAndLast = this.splitAndLast.bind(this);
    this.fetch = this.fetch.bind(this);
    this.selectGiphy = this.selectGiphy.bind(this);
    this.parseSearch = this.parseSearch.bind(this);
  }

  onFormSubmit(event) {
    event.preventDefault();
    this.onSubmit();
  }

  onSubmit() {
    this.fetch();
  }

  setFocus() {
    this.textControlRef.current.querySelector('input').focus();
    this.setState({ captionFocus: false });
  }

  // eslint-disable-next-line class-methods-use-this
  splitAndLast(array, delimiter) {
    const split = array.split(delimiter);
    return split[split.length - 1];
  }

  fetch() {
    this.setState({
      loading: true,
    });

    const searchData = this.parseSearch();
    let request = false;

    if (searchData.id) {
      request = gf.gifs([searchData.id]);
    } else {
      request = gf.search(searchData.search || '', {
        sort: 'relevant',
        limit: 10,
      });
    }

    request.then((result) => {
      this.setState({
        loading: false,
      });

      if (result.meta && result.meta.status && 200 === result.meta.status) {
        // If there is only one result, Giphy's API does not return an array.
        // The following statement normalizes the data into an array with one member in this case.
        const results = 'undefined' !== typeof result.data.images ? [result.data] : result.data;
        const giphyData = results[0];

        // No results
        if (!giphyData.images) {
          return;
        }

        this.setState({ results });
      } else {
        // Error
      }
    });
  }

  selectGiphy(giphy) {
    const { setAttributes } = this.props;

    const { images } = giphy;

    const sizes = [
      'preview_gif',
      'downsized_small',
      'downsized_medium',
      'downsized_large',
      'downsized',
    ];
    let srcset = '';

    sizes.forEach((sizeName) => {
      if (
        images[sizeName] &&
        images[sizeName].url &&
        !new RegExp(` ${images[sizeName].width}w`).test(srcset)
      ) {
        srcset += `${srcset ? ', ' : ''}${images[sizeName].url} ${images[sizeName].width}w`;
      }
    });

    setAttributes({
      url: images.original.url,
      srcset,
      caption: giphy.title,
      alt: giphy.title,
      width: parseInt(images.original.width, 10),
      height: parseInt(images.original.height, 10),
    });
  }

  parseSearch() {
    const { attributes } = this.props;
    const { searchText } = attributes;

    let giphyID = null;

    // If search is hardcoded Giphy URL following this pattern: https://giphy.com/embed/4ZFekt94LMhNK
    if (-1 !== searchText.indexOf('//giphy.com/gifs')) {
      giphyID = this.splitAndLast(this.splitAndLast(searchText, '/'), '-');
    }

    // If search is hardcoded Giphy URL following this patterh: http://i.giphy.com/4ZFekt94LMhNK.gif
    if (-1 !== searchText.indexOf('//i.giphy.com')) {
      giphyID = this.splitAndLast(searchText, '/').replace('.gif', '');
    }

    // https://media.giphy.com/media/gt0hYzKlMpfOg/giphy.gif
    const match = searchText.match(
      /http[s]?:\/\/media.giphy.com\/media\/([A-Za-z0-9\-.]+)\/giphy.gif/
    );

    if (match) {
      // eslint-disable-next-line prefer-destructuring
      giphyID = match[1];
    }

    if (giphyID) {
      return {
        id: giphyID,
      };
    }

    return {
      search: searchText,
    };
  }

  render() {
    const { attributes, isSelected, setAttributes } = this.props;

    let { className } = this.props;

    const { caption, url, srcset, alt, width, height, searchText } = attributes;

    const { captionFocus, results, loading } = this.state;

    const inputFields = (
      <Fragment>
        <form
          className="ghostkit-gif-input-container"
          onSubmit={this.onFormSubmit}
          ref={this.textControlRef}
        >
          <input
            type="text"
            className="ghostkit-gif-input components-placeholder__input"
            placeholder={__('Enter search terms, e.g. cat...', 'ghostkit')}
            onChange={(e) => setAttributes({ searchText: e.target.value })}
            value={searchText}
          />
          <Button isSecondary onClick={this.onSubmit}>
            {__('Search', 'ghostkit')}
          </Button>
        </form>
        {loading ? <Spinner /> : ''}
        {!loading && results && 1 < results.length && (
          <div className="ghostkit-gif-thumbnails-container">
            {results.map((thumbnail) => {
              const thumbnailStyle = {
                backgroundImage: `url(${thumbnail.images.downsized_still.url})`,
              };

              return (
                // eslint-disable-next-line react/button-has-type
                <button
                  className="ghostkit-gif-thumbnail-container"
                  key={thumbnail.id}
                  onClick={() => {
                    this.selectGiphy(thumbnail);
                  }}
                  style={thumbnailStyle}
                >
                  {' '}
                </button>
              );
            })}
          </div>
        )}
      </Fragment>
    );

    className = classnames('ghostkit-gif', className);

    return (
      <div className="ghostkit-gif-wrap">
        <InspectorControls>
          <PanelBody className="components-panel__body-gif-branding">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 202 22">
              <path d="M4.6 5.9H0v10h1.6v-3.1h3c4.8 0 4.8-6.9 0-6.9zm0 5.4h-3v-4h3c2.6.1 2.6 4 0 4zM51.2 12.3c2-.3 2.7-1.7 2.7-3.1 0-1.7-1.2-3.3-3.5-3.3h-4.6v10h1.6v-3.4h2.1l3 3.4h1.9l-.2-.3-3-3.3zM47.4 11V7.4h3c1.3 0 1.9.9 1.9 1.8s-.6 1.8-1.9 1.8h-3zM30.6 13.6L28 5.9h-1.1l-2.5 7.7-2.6-7.7H20l3.7 10H25l1.4-3.5L27.5 9l1.1 3.4 1.3 3.5h1.4l3.5-10h-1.7z" />
              <path d="M14.4 5.7c-3 0-5.1 2.2-5.1 5.2 0 2.6 1.6 5.1 5.1 5.1 3.5 0 5.1-2.5 5.1-5.2-.1-2.6-1.7-5.1-5.1-5.1zm-.1 8.9c-2.5 0-3.5-1.9-3.5-3.7 0-2.2 1.2-3.8 3.5-3.8 2.4 0 3.5 2 3.5 3.8.1 2-1 3.7-3.5 3.7zM57.7 11.6h5.5v-1.5h-5.5V7.4h5.7V5.9h-7.3v10h7.3v-1.6h-5.7zM38 14.3v-2.7h5.5v-1.5H38V7.4h5.7V5.9h-7.3v10h7.3v-1.6zM93 10.3l-2.7-4.4h-1.9V6l3.8 5.8v4.1h1.6v-4.1l4-5.8v-.1h-2zM69.3 5.9h-3.8v10h3.8c3.5 0 5.1-2.5 5-5.1-.1-2.5-1.6-4.9-5-4.9zm0 8.4h-2.2V7.4h2.2c2.3 0 3.4 1.7 3.4 3.4s-1 3.5-3.4 3.5zM86.3 10.7c.9-.4 1.4-1.1 1.4-2 0-2-1.5-2.8-3.4-2.8h-4.6v10h4.6c2 0 3.7-.7 3.7-2.8 0-.8-.5-2-1.7-2.4zm-5-3.4h3c1.2 0 1.8.7 1.8 1.4 0 .8-.6 1.3-1.8 1.3h-3V7.3zm3 7.1h-3v-2.9h3c.9 0 2.1.5 2.1 1.6 0 1-1.2 1.3-2.1 1.3zM113.9 13.3h5.3V16c-1.2.9-2.9 1.1-4 1.1-4.2 0-5.6-3.3-5.6-6 0-4.1 2.2-6.1 5.6-6.1 1.4 0 3.2.4 4.8 1.8l3.4-3.4C120.7.6 118.1 0 115.2 0c-7.8 0-11.4 5.6-11.4 11s3.1 10.9 11.4 10.9c4 0 7.6-1.4 8.9-4.1V8.6h-10.2v4.7zM171.9 8.5h-7.4V.6h-5.9v20.8h5.9v-7.8h7.4v7.8h5.9V.6h-5.9zM195.1.6l-4.5 7.1-4.3-7.1h-6.6v.2l7.9 12.3v8.3h5.9v-8.3L201.8.9V.6zM127.4.6h5.9v20.8h-5.9zM147.6.6h-10.1v20.8h5.9v-5.6h4.2c5.6-.1 8.3-3.4 8.3-7.6.1-4.1-2.7-7.6-8.3-7.6zm0 10.2h-4.2V5.6h4.2c1.6 0 2.5 1.2 2.5 2.6 0 1.4-.9 2.6-2.5 2.6z" />
            </svg>
          </PanelBody>
          {url ? (
            <PanelBody title={__('Image settings', 'ghostkit')}>
              {inputFields}
              <TextareaControl
                label={__('Alt text (alternative text)')}
                value={alt}
                onChange={(val) => setAttributes({ alt: val })}
                help={
                  <Fragment>
                    <ExternalLink href="https://www.w3.org/WAI/tutorials/images/decision-tree">
                      {__('Describe the purpose of the image', 'ghostkit')}
                    </ExternalLink>
                    {__('Leave empty if the image is purely decorative.', 'ghostkit')}
                  </Fragment>
                }
              />
            </PanelBody>
          ) : (
            ''
          )}
        </InspectorControls>
        {!url ? (
          <Placeholder
            className="ghostkit-gif-placeholder"
            icon={getIcon('block-gif')}
            label={__('GIF', 'ghostkit')}
            instructions={__('Search for a term or paste a Giphy URL', 'ghostkit')}
          >
            {inputFields}
          </Placeholder>
        ) : (
          <figure className={className}>
            <img src={url} srcSet={srcset} alt={alt} width={width} height={height} />
            {(!RichText.isEmpty(caption) || isSelected) && !!url && (
              <RichText
                className="ghostkit-gif-caption"
                inlineToolbar
                isSelected={captionFocus}
                unstableOnFocus={() => {
                  this.setState({ captionFocus: true });
                }}
                onChange={(value) => setAttributes({ caption: value })}
                placeholder={__('Write caption…', 'jetpack')}
                tagName="figcaption"
                value={caption}
              />
            )}
          </figure>
        )}
      </div>
    );
  }
}

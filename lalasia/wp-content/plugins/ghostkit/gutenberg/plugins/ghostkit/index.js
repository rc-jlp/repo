/**
 * Internal dependencies
 */
import getIcon from '../../utils/get-icon';
import { TemplatesModal } from '../templates';
import { TypographyModal } from '../typography';
import { CustomCodeModal } from '../custom-code';
import { ColorPaletteModal } from '../color-palette';
import { CustomizerModal } from '../customizer';

/**
 * WordPress dependencies
 */
const { Fragment } = wp.element;

const { __ } = wp.i18n;
const { Component } = wp.element;

const { PluginSidebar, PluginSidebarMoreMenuItem } = wp.editPost || {};

const { Button, PanelBody } = wp.components;

const { GHOSTKIT } = window;

export const name = 'ghostkit';

export const icon = <div className="ghostkit-plugin-icon">{getIcon('plugin-ghostkit')}</div>;

export class Plugin extends Component {
  constructor(props) {
    super(props);

    this.state = {
      isModalOpen: false,
    };
  }

  render() {
    const { isModalOpen } = this.state;

    return (
      <Fragment>
        {PluginSidebarMoreMenuItem ? (
          <PluginSidebarMoreMenuItem target="ghostkit">
            {__('Ghost Kit', 'ghostkit')}
          </PluginSidebarMoreMenuItem>
        ) : null}
        {PluginSidebar ? (
          <PluginSidebar name="ghostkit" title={__('Ghost Kit', 'ghostkit')}>
            <PanelBody className="plugin-ghostkit-panel">
              <Button
                className="plugin-ghostkit-panel-button"
                isSecondary
                isLarge
                onClick={() => {
                  this.setState({ isModalOpen: 'templates' });
                }}
              >
                {getIcon('plugin-templates')}
                {__('Templates', 'ghostkit')}
              </Button>
              <Button
                className="plugin-ghostkit-panel-button"
                isSecondary
                isLarge
                onClick={() => {
                  this.setState({ isModalOpen: 'typography' });
                }}
              >
                {getIcon('plugin-typography')}
                {__('Typography', 'ghostkit')}
              </Button>
              <Button
                className="plugin-ghostkit-panel-button"
                isSecondary
                isLarge
                onClick={() => {
                  this.setState({ isModalOpen: 'custom-code' });
                }}
              >
                {getIcon('plugin-custom-code')}
                {__('CSS & JavaScript', 'ghostkit')}
              </Button>
              <Button
                className="plugin-ghostkit-panel-button"
                isSecondary
                isLarge
                onClick={() => {
                  this.setState({ isModalOpen: 'color-palette' });
                }}
              >
                {getIcon('plugin-color-palette')}
                {__('Color Palette', 'ghostkit')}
              </Button>
              {GHOSTKIT.allowPluginCustomizer ? (
                <Button
                  className="plugin-ghostkit-panel-button"
                  isSecondary
                  isLarge
                  onClick={() => {
                    this.setState({ isModalOpen: 'customizer' });
                  }}
                >
                  {getIcon('plugin-customizer')}
                  {__('Customizer', 'ghostkit')}
                </Button>
              ) : null}
            </PanelBody>
          </PluginSidebar>
        ) : null}
        {'templates' === isModalOpen ? (
          <TemplatesModal onRequestClose={() => this.setState({ isModalOpen: false })} />
        ) : (
          ''
        )}
        {'typography' === isModalOpen ? (
          <TypographyModal onRequestClose={() => this.setState({ isModalOpen: false })} />
        ) : (
          ''
        )}
        {'custom-code' === isModalOpen ? (
          <CustomCodeModal onRequestClose={() => this.setState({ isModalOpen: false })} />
        ) : (
          ''
        )}
        {'color-palette' === isModalOpen ? (
          <ColorPaletteModal onRequestClose={() => this.setState({ isModalOpen: false })} />
        ) : (
          ''
        )}
        {'customizer' === isModalOpen ? (
          <CustomizerModal onRequestClose={() => this.setState({ isModalOpen: false })} />
        ) : (
          ''
        )}
      </Fragment>
    );
  }
}

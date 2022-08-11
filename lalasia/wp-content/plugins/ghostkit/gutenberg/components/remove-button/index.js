/**
 * Internal dependencies
 */
import getIcon from '../../utils/get-icon';

/**
 * WordPress dependencies
 */
const { Component } = wp.element;

const { __ } = wp.i18n;

const { Button, Popover } = wp.components;

/**
 * Component Class
 */
export default class RemoveButton extends Component {
  constructor(props) {
    super(props);

    this.state = {
      confirmed: -1,
    };
  }

  render() {
    const {
      onRemove,
      show,
      style,
      tooltipText = __('Remove Block?', 'ghostkit'),
      tooltipRemoveText = __('Remove', 'ghostkit'),
      tooltipCancelText = __('Cancel', 'ghostkit'),
    } = this.props;

    const { confirmed } = this.state;

    if (!show) {
      return null;
    }

    return (
      <Button
        className="ghostkit-component-remove-button"
        onClick={() => {
          if (-1 === confirmed) {
            this.setState({
              confirmed: 0,
            });
          }
        }}
        style={style}
      >
        {0 === confirmed ? (
          <Popover
            className="ghostkit-component-remove-button-confirm"
            onClose={() => {
              this.setState({
                confirmed: -1,
              });
            }}
            onFocusOutside={() => {
              this.setState({
                confirmed: -1,
              });
            }}
          >
            {tooltipText}
            <Button className="ghostkit-component-remove-button-confirm-yep" onClick={onRemove}>
              {tooltipRemoveText}
            </Button>
            <Button
              className="ghostkit-component-remove-button-confirm-nope"
              onClick={() => {
                this.setState({
                  confirmed: -1,
                });
              }}
            >
              {tooltipCancelText}
            </Button>
          </Popover>
        ) : (
          ''
        )}
        {getIcon('icon-trash')}
      </Button>
    );
  }
}

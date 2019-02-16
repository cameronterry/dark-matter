import React from 'react';

class Message extends React.Component {
  /**
   * Render.
   */
  render() {
    return (
      <div className="updated notice notice-success is-dismissible">
        <p>{ this.props.message }</p>
      </div>
    );
  }
}

export default Message;

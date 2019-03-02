import React from 'react';

class Message extends React.Component {
  handleDismiss = ( event ) => {
    event.preventDefault();

    this.props.dismiss( this.props.id, this.props.index );
  }

  /**
   * Render.
   */
  render() {
    let { notice } = this.props;
    let classes = [ 'notice', 'is-dismissable' ];

    classes.push( 'notice-' + notice.type );

    return (
      <div className={ classes.join( ' ' ) }>
        <p><strong>{ notice.domain }</strong>; { notice.text }</p>
        <button className="notice-dismiss" onClick={ this.handleDismiss }>
          <span className="screen-reader-text">Dismiss this notice.</span>
        </button>
      </div>
    );
  }
}

export default Message;

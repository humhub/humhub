var UserCompatibility = React.createClass({
  getInitialState: function() {
    return {
      source: '',
      data:   {compatibility: 1}
    }
  },

  componentDidMount: function() {
    var proto = 'http';
    var host  = 'localhost';
    var port  = '9292';
    var route = '/users/' + this.props.in_userid + '/connections/' + this.props.out_userid;
    var url   = proto + '://' + host + ':' + port + route;

    this.setState({source: url});

    $.get(url, function(result) {
      if (this.isMounted()) {
        this.setState({
          source: url,
          data:   result
        })
      }
    }.bind(this));
  },

  render: function() {
    var score = parseInt(this.state.data.score * 100);
    return(
      <div className='compatibility'>
        <span class='value'>{score}</span>
        <span className='pct'>%</span>
      </div>
    )
  }
})

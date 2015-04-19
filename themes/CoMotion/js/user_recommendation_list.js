var RecommendationList = React.createClass({
  componentWillMount: function() {
    var proto  = 'http';
    var host   = 'ec2-52-1-155-234.compute-1.amazonaws.com';
    var port   = '81';
    var route  = '/users/' + this.props.in_userid + '/recommendations/' + this.props.out_userid;
    var url    = proto + '://' + host + ':' + port + route;
    this.setState({source: url});
  },

  componentDidMount: function() {
    // do the AJAX thing here
  },

  render: function() {
    var matches = [];
    this.props.data.forEach(function(match) {
      matches.push(<RecommendedUser key={match.guid} person={match} />);
    });

    return (
      <ul className="sideswipe">
        {matches}
      </ul>
    )
  }
});

var RecommendedUser = React.createClass({
  render: function() {
    var emptyStyle = {height: 30 + (100 - this.props.person.score) + 'px'};
    var fullStyle  = {height: this.props.person.score + 'px'};

    return(
      <li key={this.props.person.guid}>
        <div className='container'>
          <img src='' />
          <div className='thermometer'>
            <span className='empty' style={emptyStyle}>
              <span>{this.props.person.score}</span>
            </span>
            <span className='full' style={fullStyle}>
              <span>%</span>
            </span>
          </div>
        </div>
        <div class='legend'>
          <span className={this.props.person.role}>&nbsp;</span>
          <p><a href={'http://localhost/'}>{this.props.person.name}</a></p>
        </div>
      </li>
    )
  }
});

var STUB_DATA = [
  {name: "Alan One", guid: "abc-def-ghi", role: 'p', score: 77},
  {name: "Beta Bob", guid: "rst-uvw-xyz", role: 'p', score: 92}
]

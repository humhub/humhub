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
    // console.log(this.props)
  },

  render: function() {
    var matches = [];
    var base_url = this.props.base_url
    this.props.data.forEach(function(match) {
      matches.push(<RecommendedUser base_url={base_url} key={match.guid} person={match} />);
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

    var emptyStyle = {height: Math.min(110, 30 + (100 - this.props.person.score)) + 'px'};
    var fullStyle  = {height: Math.max(this.props.person.score, 20) + 'px'};

    console.log(this.props)

    return(
      <li key={this.props.person.guid}>
        <div className='container'>
          <img src={this.props.base_url + '/img/default_user.jpg'} />
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
  {name: "Beta Bob", guid: "rst-uvw-xyz", role: 'p', score: 92},
  {name: "Tertiary Ted", guid: "xxx-xxx-xxx", role: 'p', score: 5}
]

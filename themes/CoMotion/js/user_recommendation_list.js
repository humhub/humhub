var RecommendationList = React.createClass({
  getInitialState: function() {
    return {
      source: '',
      data:   []
    }
  },

  componentWillMount: function() {},
  componentDidMount: function() {
    var proto = 'http';
    var host  = 'localhost';
    var port  = '9292';

    if (this.props.out_userid) {
      var route = '';
    } else {
      var route = '/users/' + this.props.in_userid + '/connections/recommended';
    }
    var url = proto + '://' + host + ':' + port + route;
    this.setState({source: url})

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
    console.log(this.state);
    var matches = [];
    var base_url = this.props.base_url
    this.state.data.forEach(function(match) {
      matches.push(<RecommendedUser base_url={base_url} key={match.id} person={match} />);
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

    var score      = parseInt(this.props.person.score * 100);
    var role_class = this.props.person.role.substring(0, 1);
    var emptyStyle = {height: Math.min(110, 30 + (100 - score)) + 'px'};
    var fullStyle  = {height: Math.max(score, 20) + 'px'};

    console.log(this.props.person);
    return(
      <li key={this.props.person.id}>
        <div className='container'>
          <img src={this.props.base_url + '/img/default_user.jpg'} />
          <div className='thermometer'>
            <span className='empty' style={emptyStyle}>
              <span>{score}</span>
            </span>
            <span className='full' style={fullStyle}>
              <span>%</span>
            </span>
          </div>
        </div>
        <div className='legend'>
          <span className={role_class}>&nbsp;</span>
         
            <a href={this.props.base_url + '/index.php?r=user/profile&uguid=' + this.props.person.id}>
              {this.props.person.name}
            </a>
        
        </div>
      </li>
    )
  }
});

var STUB_DATA = [
  {name: "Alan One", guid: "abc-def-ghi", role: 'p', score: 77},
  {name: "Beta Bob", guid: "rst-uvw-xyz", role: 'p', score: 92},
  {name: "Tertiary Ted", guid: "xxx-xxx-xxx", role: 'p', score: 5},
  {name: "Q. Quatermain", guid: "yyy-yyy-yyy", role: 'r', score: 99}
]

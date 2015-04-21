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

  componentDidUpdate: function() {
    $('#compat_box').empty();
    var score = parseInt(this.state.data.score * 100);
    var data = [
      { 'population': score },
      { 'population': (100 - score) }
    ];

    var width = 90,
        height = 90,
        radius = Math.min(width, height) / 2;

    var color = d3.scale.ordinal()
        .range(["#128BD3", "rgba(200,200,220,0.5)"]);

    var arc = d3.svg.arc()
        .outerRadius(radius )
        .innerRadius(radius - 17);

    var pie = d3.layout.pie()
        .sort(null)
        .value(function(d) { return d.population; });

    var svg = d3.select("#compat_box").insert("svg")
        .attr("width", width)
        .attr("height", height)
        .insert("g")
        .attr("transform", "translate(" + width / 2 + "," + height / 2 + ")");

    var g = svg.selectAll(".arc")
        .data(pie(data))
        .enter().append("g")
        .attr("class", "arc");

    g.append("path")
      .attr("d", arc)
      .style("fill", function(d) { return color(d.data.population); });
  },

  render: function() {
    var score = parseInt(this.state.data.score * 100);
    return(
      <div></div>
    )
  }
})

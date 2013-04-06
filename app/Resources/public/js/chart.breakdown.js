(function($) {

    "use strict";

    /**
     * jQuery UI widget for displaying a breakdown donut chart. This
     * widget is based on the d3 library.
     *
     *     $('.graph-breakdown').breakdown({
     *         gpa: 3.47,
     *         data: [{
     *             count: 118,
     *             label: "A-Grade",
     *             link: "/link/to/details?grade=a"
     *         }, {
     *             count: 78,
     *             label: "B-Grade",
     *             link: "/link/to/details?grade=b"
     *         },{
     *             count: 10,
     *             label: "D-Grade",
     *             link: "/link/to/details?grade=d"
     *         }],
     *     });
     *
     * Dependencies:
     * - jquery.ui.widget
     * - d3
     */
    $.widget('chart.breakdown', {

        /**
         * Default options
         *
         * @property {Object} options
         * @property {Number} options.gpa Grade Point Average to display
         * @property {Object[]}  options.data Array with data objects
         * @property {Object} options.colors Array with color values
         */
        options: {
            gpa: 0,
            data: [],
            colors: ["green", "greenyellow", "yellow", "orange", "orangered", "red", "darkred"]
        },
        
        /**
         * Sum of all data values
         *
         * @property {Number} total
         */
        _total: 0,
        
        /**
         * Initializes the widget
         *
         * @private
         */
        _create: function() {
            var win  = $(window),
                opt  = this.options,
                elem = this.element;
                
            this._container = d3.select(elem[0]);
            
            this._getColor = d3.scale.ordinal().range(opt.colors);
            this._pieData  = this._toPie(opt.data);
            
            this._setTotal(opt.data);
            this._initElements();
            this._initArcs();
            
            win.on('resize', $.proxy(this._initArcs, this));
        },
        
        /**
         * Initializes the arc functions. Arc functions are necessary to
         * update each time the radius is changed. Will altomaticly trigger
         * #_drawSegments to draw segments with the new arcs.
         *
         * @private
         */
        _initArcs: function() {
            var elem   = this.element,
                width = elem.width(),
                height = elem.height(),
                radius = Math.min(width, height) / 2;

            this._getArc = d3.svg.arc()
                .outerRadius(radius - 5)
                .innerRadius(radius - 25);
                            
            this._getArcOver = d3.svg.arc()            
                .outerRadius(radius)
                .innerRadius(radius - 20);

            elem.children('svg').attr("width", radius * 2)
                .attr("height", radius * 2);

            this._svgElement.attr("transform", "translate(" + radius + "," + radius + ")");
            
            this._drawSegments();
        },
        
        _arcTween: function(self, a) {
            var interpolate = d3.interpolate(this._current, a);
            this._current = interpolate(0);

            return function(t) {
                return self._getArc(interpolate(t));
            };
        },
        
        /**
         * Setup the root elements like `svg` and `.text`. Will be called once
         * by creating a new widget
         *
         * @private
         */
        _initElements: function() {
        
            var container = this._container,
                elem      = this.element,
                width     = elem.width(),
                height    = elem.height();
            
            this._textElement = $('<span><span class="value"></span> <span class="type"></span></span>')
                .attr('class', 'text')
                .find('.value')
                    .text(this.options.gpa)
                    .end()
                .find('.type')
                    .text('GPA')
                    .end();
                    
            this._textElement.appendTo(elem);
                
            this._svgElement = container.append("svg")
                .append("g");

        },
        
        /**
         * Draws the segments. Will create new, delete old and update the
         * already existing.
         *
         * @private
         */
        _drawSegments: function() {
        
            var g, path,
                self = this,
                svg  = this._svgElement;
    
            // Apply data        
            g = svg.selectAll(".arc")
                .data(this._pieData);

            // Add new ones
            g.enter().append("g")
                .attr("class", "arc")
                .on("mouseover", $.proxy(this._onSegmentMouseOver, null, this))
                .on("mouseout", $.proxy(this._onSegmentMouseOut, null, this))
                .append('a')
                    .attr('xlink:href', function(d, i) { console.info(d, i); return d.data.link; })
                    .append("path")
                        .attr("d", this._getArc)
                        .style("stroke", "white");
                        
            // Remove overhead
            g.exit().remove();
            
            path = svg.selectAll("path")
                .each(function(d) { this._current = d; })
                .data(this._pieData)
                .style("fill", function(d, index) { return self._getColor(index); })
           
            path.transition()
                .duration(1000)
                .attrTween("d", $.proxy(this._arcTween, null, this));
        },
        
        /**
         * Callback, called each time the mouse enters a segment. Starts a 
         * transition to zoom the segment and updates the text to a percentage
         * value.
         *
         * @private
         */
        _onSegmentMouseOver: function(self, data, index) {
        
            var text = self._toPercentage(data.value/self._total)
                           .replace(/\.0+%$/, "%");
                           
            d3.select(this).select('path').transition()
               .duration(350)
               .attr("d", self._getArcOver);
               
           self._textElement
               .find('.value')
                   .text(text)
                   .end()
               .find('.type')
                   .css('color', self._getColor(index))
                   .text(data.data.label);
        },
        
        /**
         * Callback, called each time the mouse leaves a segment. Starts a
         * transtision back to the initial state and puts the gpa back to
         * the text element.
         *
         * @private
         */
        _onSegmentMouseOut: function(self, data) {
        
            d3.select(this).select('path').transition()
               .duration(350)
               .attr("d", self._getArc);
               
            self._textElement
                .find('.value')
                    .text(self.options.gpa)
                    .end()
                .find('.type')
                    .css('color', "")
                    .text("GPA");
        },
        
        /**
         * Converts a simple integer to a pie layout object
         *
         * @see https://github.com/mbostock/d3/wiki/Pie-Layout
         * @param {Array} data array
         * @private
         */
        _toPie: d3.layout.pie().sort(null).value(function(d) { return d.count; }),
        
        /**
         * Converts float number to percentage value
         *
         * @private
         */
        _toPercentage: d3.format('.1%'),
        
        /**
         * Sets the total count of classes. This method will be triggered on
         * initialization and when new data is set to the widget.
         *
         * @param {Object[]} data Array with data objects
         * @private
         */
        _setTotal: function(data) {
            this._total = d3.sum(data, function(d) {return d.count});
        },
        
        /**
         * Overrides the default `setOption()` method of the jquery ui widget
         * factory. In this case special handling for setting colors, and data
         * is implemented to inititalize all the helper methods.
         *
         * @private
         */
        _setOption: function( key, value ) {
            switch( key ) {
                case "colors":
                    this._getColor = d3.scale.ordinal().range(value);
                    this._drawSegments();
                    break;
                case "data":
                    this._total   = this._setTotal(value);
                    this._pieData = this._toPie(value);
                    this._drawSegments();
                    break;
                case "gpa":
                    this._textElement.text(value)
                    break;
            }
            $.Widget.prototype._setOption.apply( this, arguments );
        },
    });

})(jQuery);
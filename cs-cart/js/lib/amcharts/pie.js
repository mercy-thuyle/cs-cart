AmCharts.AmPieChart=AmCharts.Class({inherits:AmCharts.AmSlicedChart,construct:function(e){this.className="AmPieChart";this.chartType="pie";AmCharts.AmPieChart.base.construct.call(this,e);this.pieBrightnessStep=30;this.minRadius=10;this.depth3D=0;this.startAngle=90;this.angle=this.innerRadius=0;this.startRadius="500%";this.pullOutRadius="20%";this.labelRadius=30;this.labelText="[[title]]: [[percents]]%";this.balloonText="[[title]]: [[percents]]% ([[value]])\n[[description]]";this.previousScale=1;AmCharts.applyTheme(this,
e,"AmPieChart")},drawChart:function(){AmCharts.AmPieChart.base.drawChart.call(this);var e=this.chartData;if(AmCharts.ifArray(e)){if(0<this.realWidth&&0<this.realHeight){AmCharts.VML&&(this.startAlpha=1);var g=this.startDuration,c=this.container,a=this.updateWidth();this.realWidth=a;var h=this.updateHeight();this.realHeight=h;var d=AmCharts.toCoordinate,k=d(this.marginLeft,a),b=d(this.marginRight,a),q=d(this.marginTop,h)+this.getTitleHeight(),l=d(this.marginBottom,h),v,w,f,s=AmCharts.toNumber(this.labelRadius),
r=this.measureMaxLabel();this.labelText&&this.labelsEnabled||(s=r=0);v=void 0===this.pieX?(a-k-b)/2+k:d(this.pieX,this.realWidth);w=void 0===this.pieY?(h-q-l)/2+q:d(this.pieY,h);f=d(this.radius,a,h);f||(a=0<=s?a-k-b-2*r:a-k-b,h=h-q-l,f=Math.min(a,h),h<a&&(f/=1-this.angle/90,f>a&&(f=a)),h=AmCharts.toCoordinate(this.pullOutRadius,f),f=(0<=s?f-1.8*(s+h):f-1.8*h)/2);f<this.minRadius&&(f=this.minRadius);h=d(this.pullOutRadius,f);q=AmCharts.toCoordinate(this.startRadius,f);d=d(this.innerRadius,f);d>=f&&
(d=f-1);l=AmCharts.fitToBounds(this.startAngle,0,360);0<this.depth3D&&(l=270<=l?270:90);l-=90;a=f-f*this.angle/90;for(k=0;k<e.length;k++)if(b=e[k],!0!==b.hidden&&0<b.percents){var p=360*b.percents/100,r=Math.sin((l+p/2)/180*Math.PI),x=-Math.cos((l+p/2)/180*Math.PI)*(a/f),n=this.outlineColor;n||(n=b.color);n={fill:b.color,stroke:n,"stroke-width":this.outlineThickness,"stroke-opacity":this.outlineAlpha,"fill-opacity":this.alpha};b.url&&(n.cursor="pointer");n=AmCharts.wedge(c,v,w,l,p,f,a,d,this.depth3D,
n,this.gradientRatio,b.pattern);this.addEventListeners(n,b);b.startAngle=l;e[k].wedge=n;b.ix=r;b.iy=x;b.wedge=n;b.index=k;if(this.labelsEnabled&&this.labelText&&b.percents>=this.hideLabelsPercent){var m=l+p/2,p=s;isNaN(b.labelRadius)||(p=b.labelRadius);var A=v+r*(f+p),t=w+x*(f+p),y,u=0;if(0<=p){var z;90>=m&&0<=m?(z=0,y="start",u=8):90<=m&&180>m?(z=1,y="start",u=8):180<=m&&270>m?(z=2,y="end",u=-8):270<=m&&360>m&&(z=3,y="end",u=-8);b.labelQuarter=z}else y="middle";var m=this.formatString(this.labelText,
b),B=b.labelColor;B||(B=this.color);m=AmCharts.text(c,m,B,this.fontFamily,this.fontSize,y);m.translate(A+1.5*u,t);b.tx=A+1.5*u;b.ty=t;t=d+(f-d)/2;b.pulled&&(t+=this.pullOutRadiusReal);b.balloonX=r*t+v;b.balloonY=x*t+w;0<=p?n.push(m):this.freeLabelsSet.push(m);b.label=m;b.tx=A;b.tx2=A+u;b.tx0=v+r*f;b.ty0=w+x*f}b.startX=Math.round(r*q);b.startY=Math.round(x*q);b.pullX=Math.round(r*h);b.pullY=Math.round(x*h);this.graphsSet.push(n);(0===b.alpha||0<g&&!this.chartCreated)&&n.hide();l+=360*b.percents/100}0<
s&&!this.labelRadiusField&&this.arrangeLabels();this.pieXReal=v;this.pieYReal=w;this.radiusReal=f;this.innerRadiusReal=d;0<s&&this.drawTicks();this.initialStart();this.setDepths()}(e=this.legend)&&e.invalidateSize()}else this.cleanChart();this.dispDUpd();this.chartCreated=!0},setDepths:function(){var e=this.chartData,g;for(g=0;g<e.length;g++){var c=e[g],a=c.wedge,c=c.startAngle;0<=c&&180>c?a.toFront():180<=c&&a.toBack()}},arrangeLabels:function(){var e=this.chartData,g=e.length,c,a;for(a=g-1;0<=a;a--)c=
e[a],0!==c.labelQuarter||c.hidden||this.checkOverlapping(a,c,0,!0,0);for(a=0;a<g;a++)c=e[a],1!=c.labelQuarter||c.hidden||this.checkOverlapping(a,c,1,!1,0);for(a=g-1;0<=a;a--)c=e[a],2!=c.labelQuarter||c.hidden||this.checkOverlapping(a,c,2,!0,0);for(a=0;a<g;a++)c=e[a],3!=c.labelQuarter||c.hidden||this.checkOverlapping(a,c,3,!1,0)},checkOverlapping:function(e,g,c,a,h){var d,k,b=this.chartData,q=b.length,l=g.label;if(l){if(!0===a)for(k=e+1;k<q;k++)b[k].labelQuarter==c&&(d=this.checkOverlappingReal(g,
b[k],c))&&(k=q);else for(k=e-1;0<=k;k--)b[k].labelQuarter==c&&(d=this.checkOverlappingReal(g,b[k],c))&&(k=0);!0===d&&100>h&&(d=g.ty+3*g.iy,g.ty=d,l.translate(g.tx2,d),this.checkOverlapping(e,g,c,a,h+1))}},checkOverlappingReal:function(e,g,c){var a=!1,h=e.label,d=g.label;e.labelQuarter!=c||e.hidden||g.hidden||!d||(h=h.getBBox(),c={},c.width=h.width,c.height=h.height,c.y=e.ty,c.x=e.tx,e=d.getBBox(),d={},d.width=e.width,d.height=e.height,d.y=g.ty,d.x=g.tx,AmCharts.hitTest(c,d)&&(a=!0));return a}});
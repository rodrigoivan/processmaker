bpmnGatewayInclusive=function(width,_30ab){
VectorFigure.call(this);
};
bpmnGatewayInclusive.prototype=new VectorFigure;
bpmnGatewayInclusive.prototype.type="bpmnGatewayInclusive";
bpmnGatewayInclusive.prototype.paint=function(){
VectorFigure.prototype.paint.call(this);
if(typeof workflow.sType == 'undefined')
 workflow.sType = 1;
  //Set the Task Limitation
if(typeof this.limitFlag == 'undefined' || this.limitFlag == false)
{
  this.originalWidth  = 40;
  this.originalHeight = 40;
  this.orgXPos = this.getX();
  this.orgYPos = this.getY();
  this.orgFontSize =this.fontSize;
}
this.width  = this.originalWidth * workflow.sType;
this.height = this.originalHeight  * workflow.sType;
//var x=new Array(15,50,15,-20);
//var y=new Array(50,15,-20,15);
var x=new Array(0,this.width/2,this.width,this.width/2);
var y=new Array(this.height/2,this.height,this.height/2,0);

var x2 = new Array();
var y2 = new Array();

for(var i=0;i<x.length;i++){
x2[i]=x[i]+4;
y2[i]=y[i]+1;
}
this.graphics.setStroke(this.stroke);
this.graphics.setColor( "#c0c0c0" );
this.graphics.fillPolygon(x2,y2);
this.graphics.setStroke(2);
this.graphics.setColor( "#fdf3e0" );
this.graphics.fillPolygon(x,y);
this.graphics.setColor("#a27628");
this.graphics.drawPolygon(x,y);
var x_cir = 15;
var y_cir = 15;
/*this.graphics.setStroke(3);
this.graphics.setColor( "#a27628" );
this.graphics.fillEllipse(this.getWidth()/4,this.getHeight()/4,this.getWidth()/2,this.getHeight()/2);*/
this.graphics.setColor("#a27628");
this.graphics.drawEllipse(this.getWidth()/4,this.getHeight()/4,this.getWidth()/2,this.getHeight()/2);
this.graphics.paint();
if (this.input1 != null) {
 this.input1.setPosition(0, this.height / 2);
}
if (this.input2 != null) {
  this.input2.setPosition(this.width / 2, 0);
}
if (this.output1 != null) {
 this.output1.setPosition(this.height / 2, this.width);
}
if (this.output2 != null) {
  this.output2.setPosition(this.width, this.height / 2);
}
};

bpmnGatewayInclusive.prototype.setWorkflow=function(_40c5){
VectorFigure.prototype.setWorkflow.call(this,_40c5);
if(_40c5!=null){
    var gatewayPortName = ['input1','input2','output1','output2'];
    var gatewayPortType = ['InputPort','InputPort','OutputPort','OutputPort'];
    var gatewayPositionX= [0,this.width/2,this.height/2,this.width];
    var gatewayPositionY= [this.height/2,0,this.width,this.height/2];

    for(var i=0; i< gatewayPortName.length ; i++){
        eval('this.'+gatewayPortName[i]+' = new '+gatewayPortType[i]+'()');                               //Create New Port
        eval('this.'+gatewayPortName[i]+'.setWorkflow(_40c5)');                                        //Add port to the workflow
        eval('this.'+gatewayPortName[i]+'.setName("'+gatewayPortName[i]+'")');                            //Set PortName
        eval('this.'+gatewayPortName[i]+'.setZOrder(-1)');                                             //Set Z-Order of the port to -1. It will be below all the figure
        eval('this.'+gatewayPortName[i]+'.setBackgroundColor(new Color(255, 255, 255))');              //Setting Background of the port to white
        eval('this.'+gatewayPortName[i]+'.setColor(new Color(255, 255, 255))');                        //Setting Border of the port to white
        eval('this.addPort(this.'+gatewayPortName[i]+','+gatewayPositionX[i]+', '+gatewayPositionY[i]+')');  //Setting Position of the port
     }
}
};


bpmnGatewayInclusive.prototype.getContextMenu=function(){
if(this.id != null){
   this.workflow.handleContextMenu(this);
}
};


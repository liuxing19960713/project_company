var ddate=document.getElementById("ddate");
var ddate2=document.getElementById("ddate2");
function updatedate(){
var dd1=new Date(); 
dd1.setMinutes(dd1.getMinutes()+dd1.getTimezoneOffset()-300); //取当地时间加上和格林威治的时差减要求地区和格林的时差分钟，这里是-300，代表美国东部纽约和格林的时差
//先设置setMinutes再取getHours才有效
var tmin=dd1.getHours();
if(tmin>=0 && tmin<10){
   tmin='0'+tmin;
}
var tmik=dd1.getMinutes();
if(tmik>=0 && tmik<10){
   tmik='0'+tmik;
}
var tmia=dd1.getSeconds();
if(tmia>=0 && tmia<10){
   tmia='0'+tmia;
}

//if(tmin>=6 && tmin<=12){
//   tmin='早上'+tmin;
//}
//if(tmin>12 && tmin<=18){
//   tmin='下午'+tmin;
//}
//if(tmin>18 && tmin<=24){
//   tmin='晚上'+tmin;
//}
  
ddate.innerHTML=tmin+":"+tmik+":"+tmia;
var dd2=new Date();
dd2.setMinutes(dd2.getMinutes()+dd2.getTimezoneOffset()+12*60-300);//纽约和中国相差13个小时，夏天是12个小时所以这里加上13*60 夏天改为12*60
var tmin2=dd2.getHours();
if(tmin2>=0 && tmin2<10){
   tmin2='0'+tmin2;
} 
var tmik2=dd2.getMinutes();
if(tmik2>=0 && tmik2<10){
   tmik2='0'+tmik2;
}
var tmib=dd2.getSeconds();
if(tmib>=0 && tmib<10){
   tmib='0'+tmib;
}

ddate2.innerHTML=tmin2+":"+tmik2+":"+tmib;
var t=setTimeout("updatedate()",1000);
}
updatedate();



document.write("<script language=\"javascript\" src=\"/templets/yunzaibao2/js/swt.js\"></script>");
document.write("<script type=\"text/javascript\" src=\"/swt/swt.php\"></script>");

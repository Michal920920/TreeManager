$(document).ready(function () {

    $("#add").click(function(){
        $.nette.ajax({
            type: "POST",
            url :  $("#add").attr("data-link"),
            data:{
                "treeManager-pid": $("#addpid").val(),
                "treeManager-color": getRandomColor($("#addpid").val())
            }
        });
    });

    $("#remove").click(function(){
        $.nette.ajax({
            type: "POST",
            url :  $("#removeid").attr("data-link"),
            data:{
                "treeManager-id": $("#removeid").val()
            }
        });
    });
    
    
    function getRandomColor(value) {
    var val = value;
    var hex;
    console.log(val);
    if(val == 1){
        var randomHue = Math.floor(Math.random() * 360);
        var randomSaturation = Math.floor(Math.random() * (100 - 90 + 1) + 90) ;
        var randomLightness = Math.floor(Math.random() * (55 - 45 + 1)) + 45;
        hex = hslToHex(randomHue, randomSaturation, randomLightness);
    }else{
        
    }
    return hex;
}

});

function hslToHex(h, s, l) {
  h /= 360;
  s /= 100;
  l /= 100;
  let r, g, b;
  if (s === 0) {
    r = g = b = l; // achromatic
  } else {
    const hue2rgb = (p, q, t) => {
      if (t < 0) t += 1;
      if (t > 1) t -= 1;
      if (t < 1 / 6) return p + (q - p) * 6 * t;
      if (t < 1 / 2) return q;
      if (t < 2 / 3) return p + (q - p) * (2 / 3 - t) * 6;
      return p;
    };
    const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
    const p = 2 * l - q;
    r = hue2rgb(p, q, h + 1 / 3);
    g = hue2rgb(p, q, h);
    b = hue2rgb(p, q, h - 1 / 3);
  }
  const toHex = x => {
    const hex = Math.round(x * 255).toString(16);
    return hex.length === 1 ? '0' + hex : hex;
  };
  return `#${toHex(r)}${toHex(g)}${toHex(b)}`;
}
var Rimifon = {
    "Ads": new Object,
    "NewFloatAd": function(imgUrl, strLink) {
        var ad = document.createElement("a");
        ad.id = '2460ad'
        // ad.href = 'http://h5.allugame.com/index.php/game/redirect/20?refer_by=26486';
        // ad.style.width = '50%';
        ad.DirV = true;
        ad.DirH = true;
        ad.AutoMove = true;
        ad.Image = new Image;
        ad.Seed = Math.random();
        // ad.Timer = setInterval("Rimifon.Float(" + ad.Seed + ")", 50);
        this.Ads[ad.Seed] = ad;
        ad.Image.Parent = ad;
        // ad.style.position = "fixed";
        ad.style.right = 0;
        ad.style.bottom = 0;
        ad.Image.src = "http://js.2460.xileyougame.com/img/lcyx.jpeg";
        ad.Image.style.width = '100%';
        ad.Image.onmouseover = function() {
            this.Parent.AutoMove = false;
        }
        ad.Image.onmouseout = function() {
            this.Parent.AutoMove = true;
        }
        if (strLink) {
            ad.href = strLink;
            ad.Image.border = 0;
            ad.target = "_blank";
        }
        ad.appendChild(ad.Image);
        $('.jvid-pad').append(ad);
        // var addiv = document.getElementsByClassName('jvid-pad');
        // console.log(addiv);
        // addiv.appendChild(ad);
        return ad;
    },
    "Float": function(floatId) {
        var ad = this.Ads[floatId];
        if (ad.AutoMove) {
            var curLeft = parseInt(ad.style.left);
            var curTop = parseInt(ad.style.top);
            if (ad.offsetWidth + curLeft > document.body.clientWidth + document.body.scrollLeft - 1) {
                curLeft = document.body.scrollLeft + document.body.clientWidth - ad.offsetWidth;
                ad.DirH = false;
            }
            if (ad.offsetHeight + curTop > document.body.clientHeight + document.body.scrollTop - 1) {
                curTop = document.body.scrollTop + document.body.clientHeight - ad.offsetHeight;
                ad.DirV = false;
            }
            if (curLeft < document.body.scrollLeft) {
                curLeft = document.body.scrollLeft;
                ad.DirH = true;
            }
            if (curTop < document.body.scrollTop) {
                curTop = document.body.scrollTop;
                ad.DirV = true;
            }
            ad.style.left = curLeft + (ad.DirH ? 1 : -1) + "px";
            ad.style.top = curTop + (ad.DirV ? 1 : -1) + "px";
        }
    }
}
Rimifon.NewFloatAd();
$('#2460ad').click(function() {
    window.open('http://h5.allugame.com/index.php/game/redirect/20?refer_by=26486');
});

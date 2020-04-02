var cpNickname;
var cpLevel;
var cpSrvid;
var cpAdapter;
var cpGameId;
var cpChannelId;
var cpOsType;
var pf = function (g2b, shareInfo, pf_params, passData) {
    this.g2b = g2b;
    this.shareInfo = shareInfo;
    this.pf_params = pf_params;

    this.passData = passData;
    this.reyunurl = "";
    this.init();
};
pf.prototype = new platform();

pf.prototype.init = function () {
    console.log('init done');

    this.g2b.postMessage(this.g2b.MESSAGES.INIT_CALLBACK);
    cpAdapter = this.pf_params.adapter;
    cpGameId = this.pf_params.game_id;
    cpChannelId = this.pf_params.channel_id;
    cpOsType = this.pf_params.os_type;

    // this.g2b.loadScript('http://center.funnyminigames.com/javascripts/paywindow.js', function () {
    // });

};

pf.prototype.pay = function (amount, orderData) {
    console.log("amount " + amount);
    console.log("orderData " + JSON.stringify(orderData));
    var that = this;
    var param = {};
    param.openId = orderData.openId;
    param.openKey = orderData.openKey;
    param.appId = this.passData.appId;
    param.money = amount;
    param.orderNo = orderData.orderNo;
    param.ext = orderData.ext || "";
    param.data = orderData.actor_id; //  put actor_id into data
    param.goodsName = orderData.subject;
    param.cproleid = orderData.cproleid;
    param.platform = this.passData.passId;
    var search = this.g2b.object2search(param);
    this.g2b.createPay({
        search: search
    }, function (res) {
        console.log(JSON.stringify(res));
        var generate_order_id = res.d.order_id;
        var userId = res.d.userId;
        var readl_money = amount / 100; // for test

        var url = "//" + location.host + "/index.php/api/sign_order/" + that.passData.passId + "/" + that.passData.appId +
            "?extra=" + generate_order_id +
            "&money=" + readl_money +
            "&zone_id=" + cpSrvid;

        this.g2b.getDataXHR(url, function (response) {
            if (response.c == 0) {

                var data = {
                    adapter: cpAdapter,
                    game_id: cpGameId,
                    channel_id: cpChannelId,
                    zone_id: cpSrvid,
                    openid: response.d.openid,
                    amount: readl_money,
                    extra: generate_order_id,
                    os_type: cpOsType,
                }

                var pay_url = 'http://payment.funnyminigames.com/service/create-pay-url' + that.g2b.object2search(data);
                that.g2b.getDataXHR(pay_url, function (response) {
                    // console.log(response)
                    if (response.code == 0) {
                        var payUrl = response.pay_url;
                        // 支付调起
                        payWindows(payUrl);
                    }
                })
            }
        });

    });
};
pf.prototype.checkFocus = function (data) {

    // var url = "//" + location.host + "/index.php/api/focus/" + this.passData.passId + "?openid=" + data.openId;
    // this.g2b.getDataXHR(url, function(response) {
    //     if (response.c == 0) {
    this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, -1);
    // } else {
    //     this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, -1);
    // }
    // });

};
pf.prototype.reportData = function (data) {
    console.log(JSON.stringify(data));
    if (data.action == 'enterGame') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);
        var level = data.rolelevel;
        var power = data.power;
        var currency = data.currency;
        var cproleid = data.cproleid;
        var url = "//" + location.host + "/index.php/api/login/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&level=" + level + "&power=" + power + "&currency=" + currency + "&cproleid=" + cproleid;
        cpLevel = level;
        cpSrvid = srvid;
        cpNickname = data.rolename;
        this.g2b.getDataXHR(url, function (response) {
            console.log(JSON.stringify(response));
        });
    } else if (data.action == 'create_role') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);
        var cproleid = data.cproleid;
        var url = "//" + location.host + "/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
        this.g2b.getDataXHR(url, function (response) {
            console.log(JSON.stringify(response));
        });
    } else if (data.action == 'level_up') {
        var my_roleid = data.roleid;
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);
        // var nickName = data.rolename;
        var level = data.rolelevel;
        var cproleid = data.cproleid;
    } else if (data.action == 'enterCreate') {
        var roleid = data.roleid;
        var srvid = data.srvid;

        var url = "//" + location.host + "/index.php/api/sign_collect/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
        this.g2b.getDataXHR(url, function (response) {
            console.log(JSON.stringify(response));
        });
    }
};
pf.prototype.logout = function () {};
pf.prototype.showQrCode = function () {
    console.log("pf showQrCode called");
    // document.getElementById("qr_modal").style.display = "block";
};
pf.prototype.isOpenShare = function () {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, false);
};
pf.prototype.showShare = function () {
    console.log('click share button');
    this.g2b.postMessage(this.g2b.MESSAGES.SHARE_CALLBACK, true);
}
pf.prototype.isDownloadable = function () {
    // this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, "http://img.h5sdk.zytxgame.com/img/android_apk/lcby/%E9%BE%99%E5%9F%8E%E9%9C%B8%E4%B8%9A.apk");
};

var payWindows = function(url) {
    var main_div = document.createElement("div");
    main_div.style.top = "0px";
    main_div.style.height = "100%";
    main_div.style.visibility = "inherit";
    main_div.style.width = "100%";
    main_div.style.overflow = "visible";
    main_div.style.position = "absolute";
    document.body.appendChild(main_div);
    var iframe = document.createElement("iframe");
    iframe.id = "payurl_mainframe";
    iframe.frameborder = "0";
    iframe.scrolling = "yes";
    iframe.name = "jsmain";
    iframe.style.top = "0px";
    iframe.style.height = "100%";
    iframe.style.visibility = "inherit";
    iframe.style.width = "100%";
    iframe.style.overflow = "visible";
    iframe.style.position = "absolute";
    iframe.style.zIndex = 999999999999;
    iframe.src = url;
    main_div.appendChild(iframe);
    var div = document.createElement("div");
    div.style = "position:absolute;right:0%;top:2.3%;height:40px;background:#ffffff;z-index:9999999999999;";
    var img = document.createElement("img");
    img.src = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAABkCAMAAABHPGVmAAAAolBMVEUAAAAAAAIAAAIAAAIAAAIAAAIAAAIAAAIAAAIAAAIAAAIAAAIAAAIAAAIAAAIAAAIAAAIAAAIAAAIAAAIAAAIAAAIAAAIAAAIAAAIAAAIAAAIAAAIAAAIAAAIAAAIAAAIAAAIAAAIAAAIAAAIAAAIAAAIAAAIAAAIAAAL///8lJSednZ7j4+MDAwXk5OScnJ1CQkP19fXa2torKy0FBQerq6zSOp95AAAAKHRSTlMA78on/NqmE/PgOvFrFeP3v5qVkIhuZFwsGgdzQzMiIJyyJJuxsEXkA7VTHwAAAs5JREFUaN7N2ulS2zAUhuEjJbZjYjs72bey9DOUkqT0/m+tHqCjMsRI51h28/4PzyjEyehI5Fq6mozudKA6QEcF+m40WaXks29JGONMcZh88yO0xgG+KBi3qgpRomFNJ1GVRQx6cKo3aEmJsA3n2qGEiYaGcGOGEde4VmCnrlnEtg9R/a27Me1CWHfqSCxuUKGbhYsx16iUntuNWYyKxTObcaVQOXVlMTrwUOeqfgPofqHMFDylZmXGPIO3snnJ86HhMX3+ebmB13bnjCk8N/1sbLu+ke72E9KH9/qffj9QQ9cfjUihhlT0ARmilob/Gq02aqndIlOIar2gpNBlIb+cjIfTAbAtZYCSDsdnF+NHfixTBn+NqFdq5Pmzi5Hnx5I196J3ZInzPZ3yomcXI398wvmW70hQ+vqfRhEaCN6MNWBT5AawfkXGsCtyA2PzblkUmWHerw3AUNgGsCmQBAxFYCAxXyk25VFgmK+WDAxFYCAjSgGGIjCAlFZgKCID9zQBQxEZmNAIHEViYES3YCkCA3vScMn8Zb4BTTGYCttATApM5TfXgKIuuArXQIdQxFAEBsBDcHg1TjwD5u1irQScOuYfzzCYijIfYcani6nEzg+jeQYfuIqmW64BcJU9jdgGWxnRhGvwlQmtmIZAuaeUZ0iUlCjjGBIlI6KQYYiUsEAShiFSkgLZMAyRsqGigGEIlMBsHeyGVBmbTZDVECtrs52zGWIlMBtTmyFXlpYt9svJGFKlF9mGBYejebVwHz8oH3sYxRi2iYR1ghPWNVsJPY6iXmBZSK1DtUbGg00MOpsY2TYyfG52jG7a+TV2DR5tNH9I4/+4yf/Bmfx4Tm78/8NMP8eyl3HATLTYVXoGF5dz6F+0kV5f2FzaRQzhlZLLvBzDvuYjL10GdiJYplSxte3q1drbJbLsHPCdf4nMfh1ur+O363Cx3nOuw/0BY2XGSITXImAAAAAASUVORK5CYII=";
    img.style = "width:40%";
    img.onclick = function () {
        document.body.removeChild(main_div);
    };
    document.body.appendChild(div);
    div.appendChild(img);
    main_div.appendChild(div);
}

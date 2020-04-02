var alluGameId;
var lvtoken;
var cpgameid;
var qqesuid;
var channelid;
var channeluid;
var ext;
var pf = function(g2b, shareInfo, pf_params, passData) {
    this.g2b = g2b;
    this.shareInfo = shareInfo;
    this.pf_params = pf_params;

    this.passData = passData;
    this.reyunurl = "";
    this.init();
};
pf.prototype = new platform();

pf.prototype.init = function() {

    var that = this;
    cpgameid = this.pf_params.cpgameid;
    alluGameId = this.passData.appId;
    qqesuid = this.pf_params.qqesuid;
    channelid = this.pf_params.channelid;
    channeluid = this.pf_params.channeluid;
    ext = this.pf_params.ext;

    that.g2b.loadScript("//www.7724.com/static/pc/js/jquery.js", function() {
        that.g2b.loadScript("//pulsdk.7724.com/channelsdk/sbpulsdk.js?v=1209", function() {
            lvtoken = that.pf_params.token;
            SbPulSdk.init(that.pf_params, function(channelSdk){
                //cp放置一些渠道的特殊逻辑， 比如初始化渠道的分享配置等等
                that.g2b.postMessage(that.g2b.MESSAGES.INIT_CALLBACK);
            })

        });
    });

};

pf.prototype.pay = function(amount, orderData) {
    if (alluGameId == 1034 || alluGameId == 1171) {
        return;
    };
    console.log("amount " + amount);
    console.log("orderData " + orderData);
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
    param.platform = 'seven';
    var search = this.g2b.object2search(param);
    this.g2b.createPay({
        search: search
    }, function(res) {
        var generate_order_id = res.d.order_id;
        var real_money = amount / 100;
        // var real_money = 0.01; //  for test


        var p_str = "fee=" + real_money +
            "&orderno=" + generate_order_id +
            "&subject=" + orderData.subject +
            "&uid=" + orderData.openId +
            '&token=' + lvtoken;

        var url = "//h5sdk.zytxgame.com/index.php/api/sign_order/seven/" + param.appId + "?" + 'order=' + generate_order_id +
            '&cpgameid=' + cpgameid + '&qqesuid=' + qqesuid + '&channelid=' + channelid + '&channeluid=' +
            channeluid + '&goodsname=' + 'zs' + '&fee=' + real_money + '&ext=' + (ext);

        this.g2b.getDataXHR(url, function(response) {
            if (response.c == 0) {
                cpPayParams = {
                    'order': generate_order_id,
                    'cpgameid': cpgameid,
                    'qqesuid': qqesuid,
                    'channelid': channelid,
                    'channeluid': channeluid,
                    'cpguid': response.d.cpguid,
                    'goodsname': 'zs',
                    'fee': real_money,
                    'ext': decodeURIComponent(ext),
                    'timestamp': response.d.time,
                    'sign': response.d.sign
                }
                SbPulSdk.pay(cpPayParams);
            } else {
                alert("Error: found " + response.c);
            }
        });
        closePayWindow();

    });
};
pf.prototype.checkFocus = function(data) {
    // var url = "//h5sdk.zytxgame.com/index.php/api/focus/seven?openid=" + data.openId;
    // this.g2b.getDataXHR(url, function(response) {
    //     if (response.c == 0) {
    //         this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, response.d);
    //     } else {
    this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, -1);
    //     }
    // });
};
pf.prototype.reportData = function(data) {

    if (data.action == 'enterGame') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);
        var level = data.rolelevel;
        var power = data.power;
        var currency = data.currency;
        var cproleid = data.cproleid;
        var url = "//h5sdk.zytxgame.com/index.php/api/login/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&level=" + level + "&power=" + power + "&currency=" + currency + "&cproleid=" + cproleid;

        this.g2b.getDataXHR(url, function(response) {});
    } else if (data.action == 'create_role') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);

        var cproleid = data.cproleid;
        var url = "//h5sdk.zytxgame.com/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
        this.g2b.getDataXHR(url, function(response) {});
    } else if (data.action == 'enterCreate') {
        var roleid = data.roleid;
        var srvid = data.srvid;

        var url = "//h5sdk.zytxgame.com/index.php/api/sign_collect/seven/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
        this.g2b.getDataXHR(url, function(response) {});
    }
};
pf.prototype.logout = function() {};
pf.prototype.showQrCode = function() {
    console.log("pf showQrCode called");
    QqesSdk.follow();
};
pf.prototype.isOpenShare = function() {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, true);
};


pf.prototype.showShare = function() {
    console.log("shareShare");
    var that = this;
    // console.log(that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true));
    var sever_app_key = 0;

    if (this.passData.appId == 1008) {
        sever_app_key = "148654992405";
    } else if (this.passData.appId == 1034) {
        sever_app_key = "149380912453";
    }else if(this.passData.appId == 1170){
        sever_app_key = "152317608177";
    }else if(this.passData.appId == 1171){
        sever_app_key = "152317609245";
    }

    QqesSdk.share(sever_app_key, function() {
        that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true);
    });
}
pf.prototype.isDownloadable = function() {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, false);
};

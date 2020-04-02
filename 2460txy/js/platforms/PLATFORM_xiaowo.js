var cpNickname;
var cpLevel;
var cp_game_appid;
var cp_consumecode;
var cpToken;
var cpchannelid;
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
    console.log('init done');
    var that = this;
    this.g2b.loadScript("//h5game.wostore.cn/platform/game/src/js/jquery.js", function() {
        that.g2b.loadScript("//h5game.wostore.cn/platform/game/info.js", function() {});

        cp_game_appid = that.pf_params.appId;
        cpchannelid = that.pf_params.channelid;
        cpToken = that.pf_params.access_token;

        that.g2b.postMessage(that.g2b.MESSAGES.INIT_CALLBACK);
    });
};

pf.prototype.pay = function(amount, orderData) {
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
    }, function(res) {
        // console.log(JSON.stringify(res));
        var generate_order_id = res.d.order_id;
        var userId = res.d.userId;
        var readl_money = amount // for test

        switch (readl_money) {
            case 64800:
                cp_consumecode = "90100010100720181119170208702600001";
                break;
            case 28800:
                cp_consumecode = "90100010100720181119170208702600002";
                break;
            case 18800:
                cp_consumecode = "90100010100720181119170208702600003";
                break;
            case 9800:
                cp_consumecode = "90100010100720181119170208702600004";
                break;
            case 6800:
                cp_consumecode = "90100010100720181119170208702600005";
                break;
            case 4800:
                cp_consumecode = "90100010100720181119170208702600006";
                break;
            case 5000:
                cp_consumecode = "90100010100720181119170208702600007";
                break;
            case 2500:
                cp_consumecode = "90100010100720181119170208702600008";
                break;
            case 1000:
                cp_consumecode = "90100010100720181119170208702600009";
                break;
            case 300000:
                cp_consumecode = "90100010100720181119170208702600010";
                break;
            case 250000:
                cp_consumecode = "90100010100720181119170208702600011";
                break;
            case 200000:
                cp_consumecode = "90100010100720181119170208702600012";
                break;
            case 180000:
                cp_consumecode = "90100010100720181119170208702600013";
                break;
            case 150000:
                cp_consumecode = "90100010100720181119170208702600014";
                break;
            case 120000:
                cp_consumecode = "90100010100720181119170208702600015";
                break;
            case 100000:
                cp_consumecode = "90100010100720181119170208702600016";
                break;
            case 80000:
                cp_consumecode = "90100010100720181119170208702600017";
                break;
            case 60000:
                cp_consumecode = "90100010100720181119170208702600018";
                break;
            case 50000:
                cp_consumecode = "90100010100720181119170208702600019";
                break;
            case 40000:
                cp_consumecode = "90100010100720181119170208702600020";
                break;
            case 30000:
                cp_consumecode = "90100010100720181119170208702600021";
                break;
            case 20000:
                cp_consumecode = "90100010100720181119170208702600022";
                break;
            case 10000:
                cp_consumecode = "90100010100720181119170208702600023";
                break;
            case 5000:
                cp_consumecode = "90100010100720181119170208702600024";
                break;
            case 3000:
                cp_consumecode = "90100010100720181119170208702600025";
                break;
            case 2000:
                cp_consumecode = "90100010100720181119170208702600026";
                break;
            case 1000:
                cp_consumecode = "90100010100720181119170208702600027";
                break;
            case 1:
                cp_consumecode = "90100010100720181119170208702600028";
                break;
            default:
                cp_consumecode = '';
                console.log(cp_consumecode);
                break;
        }

        var url = "//" + location.host + "/index.php/api/sign_order/" + that.passData.passId + "/" + that.passData.appId +
            "?cporderid=" + generate_order_id +
            "&feetype=1" +
            "&money=" + readl_money +
            "&consumecode=" + cp_consumecode +
            "&cptoken=" + cpToken +
            "&cpchannelid=" + cpchannelid +
            "&consumename=" + param.goodsName;

        this.g2b.getDataXHR(url, function(response) {
            if (response.c == 0) {
                var data = {
                    cporderid: generate_order_id,
                    cpid: response.d.cpid,
                    feetype: response.d.feetype,
                    payfee: readl_money,
                    channelid: response.d.channelid,
                    consumecode: response.d.consumecode,
                    consumename: encodeURIComponent(param.goodsName),
                    userID: response.d.userID,
                    appid: response.d.appid,
                    from: 'h5game',
                    cpsign: response.d.cpsign
                };

                var res_url = 'https://h5.wostore.cn/payment/multipay.html' + that.g2b.object2search(data);
                console.log(res_url);
                wogamesdk.openPay(res_url,function(e){
                    console.info(e.status + "=======" + e.orderid);
                });
            }
        });

    });
};
pf.prototype.checkFocus = function(data) {

    // var url = "//" + location.host + "/index.php/api/focus/" + this.passData.passId + "?openid=" + data.openId;
    // this.g2b.getDataXHR(url, function(response) {
    //     if (response.c == 0) {
    this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, -1);
    // } else {
    //     this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, -1);
    // }
    // });

};
pf.prototype.reportData = function(data) {
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
        cpNickname = data.rolename;
        this.g2b.getDataXHR(url, function(response) {
            console.log(JSON.stringify(response));
        });
        // 进入游戏上报接口, 给渠道
        var _url = "//" + location.host + "/index.php/api/focus/" + this.passData.passId + "/" + this.passData.appId;
        this.g2b.getDataXHR(_url, function(response) {
            console.log(JSON.stringify(response.m));
            var cp_userId = response.d.userId;
            var cp_appId = response.d.appId;
            wogamesdk.roleInfo({
                "appid": cp_appId,
                "userId": cp_userId,
                "serverId": srvid,
                "serverName": '',
                "isNewRole": true,
                "roleId": roleid,
                "roleName": cpNickname,
                "roleLevel": level,
                "roleCoins": 'vip'+level
            })
        });

    } else if (data.action == 'create_role') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);
        var cproleid = data.cproleid;
        var url = "//" + location.host + "/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
        this.g2b.getDataXHR(url, function(response) {
            console.log(JSON.stringify(response));
        });
    } else if (data.action == 'enterCreate') {
        var roleid = data.roleid;
        var srvid = data.srvid;

        var url = "//" + location.host + "/index.php/api/sign_collect/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
        this.g2b.getDataXHR(url, function(response) {
            console.log(JSON.stringify(response));
        });
    }
};
pf.prototype.logout = function() {};
pf.prototype.showQrCode = function() {
    console.log("pf showQrCode called");
    // document.getElementById("qr_modal").style.display = "block";
};
pf.prototype.isOpenShare = function() {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, false);
};
pf.prototype.showShare = function() {
    console.log('click share button');
    this.g2b.postMessage(this.g2b.MESSAGES.SHARE_CALLBACK, true);
}
pf.prototype.isDownloadable = function() {
    // this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, "http://img.h5sdk.zytxgame.com/img/android_apk/lcby/%E9%BE%99%E5%9F%8E%E9%9C%B8%E4%B8%9A.apk");
};

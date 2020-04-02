var alluGameId;
var ylgame;
var gid;
var sdk;
var cpuid;
var login_power;
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
    gid = this.pf_params.gid;
    cpuid = this.pf_params.uid;



    this.g2b.loadScript("://" + location.host + "/js/jquery-3.1.1.js", function () {
        // this.g2b.loadScript("http://m.59yx.com/Public/jssdk/js/jssdk.js", function() {
        this.g2b.loadScript('//m.lbwan.com/Public/jssdk/js/jssdk.js', function () {
            this.g2b.loadScript('//sdk.lbwan.com/static/js/sdk/pay.js', function () {
                 sdk = new PAYSDK();
                // sdk = new PAYSDK();
                this.g2b.postMessage(this.g2b.MESSAGES.INIT_CALLBACK);
                // });
            });
        });


    });


};

pf.prototype.pay = function (amount, orderData) {
    if (alluGameId == 1037) {
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
    param.platform = 'lbw';
    var search = this.g2b.object2search(param);
    this.g2b.createPay({
        search: search
    }, function (res) {
        var generate_order_id = res.d.order_id;
        var userId = res.d.userId;
        var readl_money = amount / 100;
        // var readl_money = 1;

        // var ylgame = new ylGame();
        console.log(ylgame);
        var data = {
            gid: gid,
            orderId: generate_order_id,
            total_fee: parseInt(readl_money),
            gold: param.goodsName,
            uid: userId,
            serverNum: parseInt(orderData.ext),
            playerName: orderData.actor_id,
            time: Date.parse(new Date()) / 1000,

            diamond: amount,
            // diamond: readl_money,
            playerId: param.cproleid,
        };

        var sign_params = this.g2b.object2search(data);

        var url = "http://" + location.host + "/index.php/api/sign_order/lbw/" + param.appId + sign_params;
        this.g2b.getDataXHR(url, function (response) {
            if (response.c == 0) {
                var sign = response.d.sign;
                data.sign = sign;
                data.gid = response.d.gid;
                // data.playerId= param.cproleid;
                console.log(JSON.stringify(data));
                sdk.sdkPay(data,  function (result) {
                    // sdk.sdkPay(data, function(result) {
                    console.log("result.code " + result.code + " result.message " + result.message);
                    //支付回调
                    //result结构如下
                    // result.code //0=成功 -1=取消，其他<0=失败
                    //
                    // result.message //错误描述
                    // result.showMessage //显示提示
                    // result.data //返回数据
                });
            }
        });



    });
};
pf.prototype.checkFocus = function (data) {
    this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, -1);

};
pf.prototype.reportData = function (data) {
    if (data.action == 'enterGame') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);
        var level = data.rolelevel;
        var power = data.power;
        var currency = data.currency;
        var cproleid = data.cproleid;
        var url = "//" + location.host + "/index.php/api/login/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&level=" + level + "&power=" + power + "&currency=" + currency + "&cproleid=" + cproleid;
        login_power = power;
        this.g2b.getDataXHR(url, function (response) {});
        var roleInfoJSON = {
            datatype: 1,
            gid: gid,
            uid: cpuid,
            serverid: srvid,
            servername: srvid,
            roleid: cproleid,
            rolename: data.rolename,
            rolelevel: level,
            fightvalue: power
        }
        sdk.rolsInfo(roleInfoJSON);
        var roleInfoJSON = {
            datatype: 3,
            gid: gid,
            uid: cpuid,
            serverid: srvid,
            servername: srvid,
            roleid: cproleid,
            rolename: data.rolename,
            rolelevel: level,
            fightvalue: power
        }
        sdk.rolsInfo(roleInfoJSON);
    } else if (data.action == 'create_role') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);

        var cproleid = data.cproleid;
        var url = "//" + location.host + "/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
        this.g2b.getDataXHR(url, function (response) {});
        var roleInfoJSON = {
            datatype: 2,
            gid: gid,
            uid: cpuid,
            serverid: srvid,
            servername: srvid,
            roleid: cproleid,
            rolename: data.rolename,
            rolelevel: level,
            fightvalue: power
        }
        sdk.rolsInfo(roleInfoJSON);
    } else if (data.action == 'level_up') {
        var my_roleid = data.roleid;
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);
        // var nickName = data.rolename;
        var level = data.rolelevel;
        var cproleid = data.cproleid;
        var roleInfoJSON = {
            datatype: 4,
            gid: gid,
            uid: cpuid,
            serverid: srvid,
            servername: srvid,
            roleid: cproleid,
            rolename: data.rolename,
            rolelevel: level,
            fightvalue: login_power
        }
        sdk.rolsInfo(roleInfoJSON);
    } else if (data.action == 'enterCreate') {
        var roleid = data.roleid;
        var srvid = data.srvid;

        var url = "//" + location.host + "/index.php/api/sign_collect/lbw/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
        this.g2b.getDataXHR(url, function (response) {});
    }
};
pf.prototype.logout = function () {};
pf.prototype.showQrCode = function () {

};
pf.prototype.isOpenShare = function () {
    var that = this;
    var share = {
        gid: gid,
        uid: cpuid,
        time: Date.parse(new Date()) / 1000,
    };
    // sdk.shareInitialize(share, function(result) {
    //     if (result.code != '-10') {
    //         console.log("this.passData.appId " + this.passData.appId);
    //         this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, true);
    //     } //0=可用 -10=分享不可以用，隐藏分享按钮

    // });
    that.g2b.postMessage(that.g2b.MESSAGES.RETURNSHARE, true);
    // ylgame.openShare(function() {
    //     //分享成功时回调方法
    //     that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true);
    // },gid);


    // if (this.passData.appId != 1037) {
    //     this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, false);
    // }

};

pf.prototype.showShare = function () {
    var that = this;
    console.log("shareShare");
    // alert('分享至微信好友获得奖励');
    var share = {
        gid: gid,
        uid: cpuid,
        time: Date.parse(new Date()) / 1000,
    };
    sdk.share(share, function(result) {
        if (result.code == 0) {

            that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true);
        } //0=成功 -1=取消，其他<0=失败
    });
    // sdk.openShare(function () {
    //     //分享成功时回调方法
    //     that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true);
    // }, gid);

};



pf.prototype.isDownloadable = function () {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, false);
};

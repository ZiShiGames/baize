var cp_uid = '';
var cp_time = '';
var cp_gameid = '';

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

    cp_gameid = this.pf_params.gameId;
    cp_uid = this.pf_params.uid;
    cp_time = this.pf_params.time;
    var that = this;


    this.g2b.loadScript('http://wap.hjygame.com/static/libs/jquery/jquery.js', function() {});
    this.g2b.loadScript('http://res.wx.qq.com/open/js/jweixin-1.2.0.js', function() {});
    this.g2b.loadScript('http://wap.hjygame.com/static/js/share.js', function() {});

    this.g2b.loadScript('http://wap.hjygame.com/sdk/cy.sdk.js', function() {
        var sdk = window.CY_GAME_SDK;

        var params = {
            gameId: cp_gameid,
            pay: {
                success: function() {
                    console.log('success');
                }
            },
            share: {
                success: function() {
                    that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true);
                }
            }
        };

        sdk.config(params);
        this.g2b.postMessage(this.g2b.MESSAGES.INIT_CALLBACK);
    });
};

pf.prototype.pay = function(amount, orderData) {
    return;
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
        console.log(JSON.stringify(res));
        var generate_order_id = res.d.order_id;
        var userId = res.d.userId;
        var readl_money = amount / 100; // for test


        var url = "http://" + location.host + "/index.php/api/sign_order/" + that.passData.passId + "/" + that.passData.appId +
            "?gameId=" + cp_gameid +
            "&uid=" + userId +
            "&server=" + param.ext +
            "&role=" + param.ext +
            "&goodsId=" + readl_money +
            "&goodsName=" + param.goodsName +
            "&money=" + readl_money +
            "&cpOrderId=" + generate_order_id;

        this.g2b.getDataXHR(url, function(response) {
            if (response.c == 0) {
                var gameId = response.d.gameId;
                var uid = response.d.uid;
                var server = response.d.server;
                var role = response.d.role;
                var time = response.d.time;
                var goodsId = response.d.goodsId;
                var goodsName = response.d.goodsName;
                var money = response.d.money;
                var cpOrderId = response.d.cpOrderId;
                var sign = response.d.sign;

                sdk.pay({
                    uid: uid,
                    gameId: gameId,
                    time: time,
                    server: server,
                    role: role,
                    goodsId: goodsId,
                    goodsName: goodsName,
                    money: money,
                    cpOrderId: cpOrderId,
                    ext: "1",
                    sign: sign,
                    signType: "md5"
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

        // var _url = "//" + location.host + "/index.php/api/login/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&level=" + level + "&power=" + power + "&currency=" + currency + "&cproleid=" + cproleid;
        var _url = "//" + location.host + "/index.php/api/focus/" + this.passData.passId + "/" + this.passData.appId + "?gameId=" + cp_gameid + "&isCreateRole=" + 'false' + "&roleCreateTime=" + '0' + "&uid=" + cp_uid + "&username=" + nickName + "&serverId=" + srvid +
            "&serverName=" + srvid + "&userRoleId=" + cproleid + "&userRoleName=" + nickName + "&userRoleBalance=" + currency + "&vipLevel=" + '0' + "&userRoleLevel=" + level +
            "&gameRoleMoney=" + '0' + "&partyId=" + '1' + "&partyName=" + '1' + "&gameRoleGender=" + 'no' + "&gameRolePower=" + power;
        this.g2b.getDataXHR(_url, function(response) {
            console.log(JSON.stringify(response));
        });
        this.g2b.getDataXHR(url, function(response) {
            console.log(JSON.stringify(response));
        });
    } else if (data.action == 'create_role') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);
        var cproleid = data.cproleid;
        var url = "//" + location.host + "/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;

        var _url = "//" + location.host + "/index.php/api/focus/" + this.passData.passId + "/" + this.passData.appId + "?gameId=" + cp_gameid + "&isCreateRole=" + 'true' + "&roleCreateTime=" + '0' + "&uid=" + cp_uid + "&username=" + nickName + "&serverId=" + srvid +
            "&serverName=" + srvid + "&userRoleId=" + cproleid + "&userRoleName=" + nickName + "&userRoleBalance=" + currency + "&vipLevel=" + '0' + "&userRoleLevel=" + level +
            "&gameRoleMoney=" + '0' + "&partyId=" + '1' + "&partyName=" + '1' + "&gameRoleGender=" + 'no' + "&gameRolePower=" + power;
        this.g2b.getDataXHR(_url, function(response) {
            console.log(JSON.stringify(response));
        });
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

};
pf.prototype.isOpenShare = function() {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, true);
};
pf.prototype.showShare = function() {
    console.log('click share button');
    theShare();
}
pf.prototype.isDownloadable = function() {
    // this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, "http://img.h5sdk.zytxgame.com/img/android_apk/lcby/%E9%BE%99%E5%9F%8E%E9%9C%B8%E4%B8%9A.apk");
};
window.addEventListener("message", function(event) {
    if (event.data.operation == 'onshare') {
        this.g2b.postMessage(this.g2b.MESSAGES.SHARE_CALLBACK, true);
    }

}, false);

function theShare() {
    console.log("start share");
    if (isWeixin()) {
        var share = window.SHARE;
        share.init();
    } else {
        window.top.postMessage({
            operation: "clickShare"
        }, "*");
    }
}

function ajax_post(url, data, callback) {
    $.ajax({
        type: "get",
        url: url,
        dataType: 'json',
        async: false,
        data: data,
        success: function(data) {
            callback(data);
        },
        error: function() {
            alert("连接失败，请刷新后重试");
        }
    });
}

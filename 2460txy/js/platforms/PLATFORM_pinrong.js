var cpNickname;
var cpLevel;
var cpSdkindx;
var cpUid;
var cpSrvid;
var cpRoleid;
var cpPower;
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
    cpSdkindx = this.pf_params.sdkindx;
    cpUid = this.pf_params.uid;
    this.g2b.postMessage(this.g2b.MESSAGES.INIT_CALLBACK);
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
        var readl_money = amount; // for test
        var cpFeeid;

        switch (readl_money) {
            case 200000:
                cpFeeid = "2001";
                break;
            case 50000:
                cpFeeid = "2002";
                break;
            case 10000:
                cpFeeid = "2003";
                break;
            case 3000:
                cpFeeid = "2004";
                break;
            case 600:
                cpFeeid = "2005";
                break;
            default:
                cpFeeid = readl_money * 100;
                break;
        }

        var url = "//" + location.host + "/index.php/api/sign_order/" + that.passData.passId + "/" + that.passData.appId + "?fee=" + readl_money + "&feeid=" + cpFeeid;

        this.g2b.getDataXHR(url, function (response) {
            if (response.c == 0) {
                var payinfojson = {
                    "check": response.d.check,
                    "feeid": cpFeeid,
                    "fee": readl_money,
                    "feename": param.goodsName,
                    "extradata": generate_order_id,
                    "serverid": cpSrvid,
                    "rolename": cpNickname,
                    "roleid": cpRoleid,
                    "servername": cpSrvid
                }

                ZmSdk.getInstance().pay(payinfojson, function (data) {
                    console.log(data);
                    if (data.retcode === "0") {
                        console.log("pay ok")
                    }
                });
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
    // var showData = {

    // }
    // ZmSdk.getInstance().showQRCode(showData, function(){

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
        cpRoleid = cproleid;
        cpPower = power;
        cpNickname = data.rolename;
        this.g2b.getDataXHR(url, function (response) {
            console.log(JSON.stringify(response));
        });
        // 玩家进入游戏数据 上报给渠道
        var roleInfoJSON = {
            "datatype": "3",
            "serverid": cpSrvid,
            "servername": cpSrvid,
            "roleid": cpRoleid,
            "rolename": cpNickname,
            "rolelevel ": cpLevel,
            "fightvalue": cpPower
        }
        ZmSdk.getInstance().reportRoleStatus(roleInfoJSON);
        var roleInfoJSON = {
            "datatype": "1",
            "serverid": cpSrvid,
            "servername": cpSrvid,
            "roleid": cpRoleid,
            "rolename": cpNickname,
            "rolelevel ": cpLevel,
            "fightvalue": cpPower
        }
        ZmSdk.getInstance().reportRoleStatus(roleInfoJSON);
    } else if (data.action == 'create_role') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);
        var cproleid = data.cproleid;
        var url = "//" + location.host + "/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
        this.g2b.getDataXHR(url, function (response) {
            console.log(JSON.stringify(response));
        });
        // 玩家进入游戏数据 上报给渠道
        var roleInfoJSON = {
            "datatype": "2",
            "serverid": srvid,
            "servername": srvid,
            "roleid": cproleid,
            "rolename": data.rolename,
            "rolelevel ": 1,
            "fightvalue": cproleid
        }
        ZmSdk.getInstance().reportRoleStatus(roleInfoJSON);
    } else if (data.action == 'level_up') {
        var my_roleid = data.roleid;
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);
        // var nickName = data.rolename;
        var level = data.rolelevel;
        var cproleid = data.cproleid;
        var roleInfoJSON = {
            "datatype": "4",
            "serverid": srvid,
            "servername": srvid,
            "roleid": cproleid,
            "rolename": data.rolename,
            "rolelevel ": level,
            "fightvalue": cproleid
        }
        ZmSdk.getInstance().reportRoleStatus(roleInfoJSON);
    } else if (data.action == 'enterCreate') {
        var roleid = data.roleid;
        var srvid = data.srvid;

        var url = "//" + location.host + "/index.php/api/sign_collect/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
        this.g2b.getDataXHR(url, function (response) {
            console.log(JSON.stringify(response));
        });
    };
};

pf.prototype.logout = function () {};
pf.prototype.showQrCode = function () {
    console.log("pf showQrCode called");
    // document.getElementById("qr_modal").style.display = "block";
};
pf.prototype.isOpenShare = function () {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, true);
};
pf.prototype.showShare = function () {
    var that = this;
    alert('请分享至好友');
    console.log('click share button');
    // 点击分享时数据 上报给渠道
    var roleInfoJSON = {
        "datatype": "1",
        "serverid": cpSrvid,
        "servername": cpSrvid,
        "roleid": cpRoleid,
        "rolename": cpNickname,
        "rolelevel": cpLevel,
        "fightvalue": cpPower
    }
    ZmSdk.getInstance().reportRoleDetail(roleInfoJSON);
    // 设置分享信息
    var shareData = {
        "title": that.shareInfo.title,
        "content": that.shareInfo.describe,
        "imgurl": that.shareInfo.icon,
        "ext": cpSrvid
    }
    ZmSdk.getInstance().setShareInfo(shareData, function(data){
        that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true);
    });
}
pf.prototype.isDownloadable = function () {
    // this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, "http://img.h5sdk.zytxgame.com/img/android_apk/lcby/%E9%BE%99%E5%9F%8E%E9%9C%B8%E4%B8%9A.apk");
};

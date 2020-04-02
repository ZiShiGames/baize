var u;
var gkey;
var pkey;
var my_openid;
var my_skey;
var isOpenShare = false;
var pf = function(g2b, shareInfo, pf_params, passData) {
    this.g2b = g2b;
    this.shareInfo = shareInfo;
    this.pf_params = pf_params;

    this.passData = passData;
    p_name = passData.passId;
    this.reyunurl = "";
    this.init();
};
pf.prototype = new platform();

pf.prototype.init = function() {
    console.log('init done');
    my_openid = this.pf_params.openid;
    var that = this;
    window.__wanPostMessageWindow = window.parent.parent;
    this.g2b.loadScript("//698wan.188wan.com/sdk/load/open-sdk", function(response) {
        gkey = that.pf_params.gkey;
        pkey = that.pf_params.pkey;
        that.g2b.postMessage(that.g2b.MESSAGES.INIT_CALLBACK);
        wanOpenSdk.ready(function() {
            var setting = wanOpenSdk.getSetting();
            if (setting.share) {
                isOpenShare = true;
            }
            if (setting.showWechatAccount) {} else {}
        });
    });
};

pf.prototype.pay = function(amount, orderData) {
    var param = {};
    param.openId = orderData.openId;
    param.openKey = orderData.openKey;
    param.appId = this.passData.appId;
    param.money = amount;
    param.orderNo = orderData.orderNo;
    // param.orderNo = orderData.orderNo;
    param.ext = orderData.ext || "";
    param.data = orderData.actor_id; //  put actor_id into data
    param.goodsName = orderData.subject;
    param.cproleid = orderData.cproleid;
    param.platform = passId;
    var search = this.g2b.object2search(param);
    this.g2b.createPay({
        search: search
    }, function(res) {
        var generate_order_id = res.d.order_id;
        var user_id = res.d.userId;
        var readl_money = amount / 100;
        // var readl_money = 0.01;
        var remark = orderData.subject;
        this.g2b.getDataXHR("http://" + location.host + "/index.php/api/sign_order/" +
            param.platform + "/" +
            param.appId +
            "?openid=" + user_id +
            "&gkey=" + gkey +
            "&skey=" + my_skey +
            "&money=" + readl_money +
            "&orderno=" + generate_order_id +
            '&remark=' + remark,
            function(response) {
                if (response.c == 0) {
                    var params = {
                        "openid": response.d.openid,
                        "gkey": response.d.gkey,
                        "skey": response.d.skey,
                        "money": response.d.money,
                        "gold": response.d.gold,
                        "orderno": response.d.orderno,
                        "remark": response.d.remark,
                        "time": response.d.time,
                        "sign": response.d.sign
                    };
                    console.log(params);
                    wanOpenSdk.pay(params, function(ret) {
                        if (ret.errno == 0 && ret.errmsg == 'ok') {
                            // 使用以上方式判断前端返回,郑重提示：res.message将在用户支付成功后返回ok，但并不保证它绝对可靠，以服务器订单状态通知为准
                        }
                    });

                }
            })

    });
};
pf.prototype.checkFocus = function(data) {
    this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, 0);
};
pf.prototype.reportData = function(data) {
    // 获取10位数的时间戳
    var timestamp = Date.parse(new Date());
    timestamp = timestamp / 1000;
    console.log('reportData');
    console.log(data);

    if (data.action == 'enterGame') {
        var my_roleid = data.roleid;
        var roleid = data.roleid;
        var srvid = data.srvid;
        my_skey = srvid;
        var nickName = encodeURIComponent(data.rolename);
        // var nickName = data.rolename;
        var level = data.rolelevel;
        var power = data.power;
        var currency = data.currency;
        var cproleid = data.cproleid;
        var url = "//" + location.host + "/index.php/api/login/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&level=" + level + "&power=" + power + "&currency=" + currency + "&cproleid=" + cproleid;
        console.log('login api');
        console.log(url);

        this.g2b.getDataXHR(url, function(response) {});
        var params = {
            "gkey": gkey,
            "skey": my_skey,
            "openid": my_openid,
            "time": timestamp,
            "type": "access",
            "data": {
                "level": level,
                "roleid": cproleid,
                "rolename": data.rolename ,

            }
        };
        console.log('access');
        console.log(params);
        wanOpenSdk.push(params);




    } else if (data.action == 'create_role') {
        var my_roleid = data.roleid;
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);
        // var nickName = data.rolename;
        var cproleid = data.cproleid;
        var url = "//" + location.host + "/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
        this.g2b.getDataXHR(url, function(response) {});
        var params = {
            "gkey": gkey,
            "skey": srvid,
            "openid": my_openid,
            "time": timestamp,
            "type": "createrolescene",
        };
        wanOpenSdk.push(params);
        var params = {
            "gkey": gkey,
            "skey": srvid,
            "openid": my_openid,
            "time": timestamp,
            "type": "createrole",
            "data": {
                roleid: cproleid,
                rolename: data.rolename
            }
        };
        wanOpenSdk.push(params);
        var params = {
            "gkey": gkey,
            "skey": srvid,
            "openid": my_openid,
            "time": timestamp,
            "type": "firstscene",
            "data": {
                roleid: cproleid,
                rolename: data.rolename,
                level: 0
            } // level=转生次数*10000+角色等级
        };
        wanOpenSdk.push(params);
    } else if (data.action == 'level_up') {
        var my_roleid = data.roleid;
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);
        // var nickName = data.rolename;
        var level = data.rolelevel;
        var cproleid = data.cproleid;
        // var url = "//" + location.host + "/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
        // this.g2b.getDataXHR(url, function(response) {});
        var params = {
            "gkey": gkey,
            "skey": srvid,
            "openid": my_openid,
            "time": timestamp,
            "type": "levelup",
            "data": {
                "roleid": cproleid,
                "rolename": data.rolename,
                "level": level
            } // level=转生次数*10000+角色等级
        };
        console.log('level up');
        console.log(params);
        wanOpenSdk.push(params);


    } else if (data.action == 'enterCreate') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var url = "//" + location.host + "/index.php/api/sign_collect/" + passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
        this.g2b.getDataXHR(url, function(response) {});


    }
};
pf.prototype.showQrCode = function() {
    console.log("pf showQrCode called");
    wanOpenSdk.showWechatAccount();
};

pf.prototype.isOpenShare = function() {
    if (isOpenShare) {
        this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, true);
    }
};

pf.prototype.isDownloadable = function() {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, false);
};

pf.prototype.showShare = function() {
    var that = this;
    wanOpenSdk.share({}, function(ret) {
        console.log("share callback");
        console.log(ret);
        if (ret.errno == 0) {
            that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true);
            return SUCCESS;
        } else {
            return ERROR;
        }
    });
}
// window.addEventListener("message", function(e) {
//     console.log("message");
//     console.log(e);
// })
// window.attachEvent("onmessage", function(e) {
//     console.log('onmessage');
//     console.log(e);
// })

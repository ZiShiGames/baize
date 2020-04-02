var ext_sdk = '';
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
    this.g2b.loadScript('https://img5.073img.com/sdk/js/h5sdk07073.cp.3.min.js', function() {
        this.g2b.getDataXHR("http://h5sdk.zytxgame.com/index.php/api/focus/" + that.passData.passId + "/" + that.passData.appId, function(res) {
            if (res.c == 0) {
                if (that.pf_params.ext_sdk) {
                    ext_sdk = that.pf_params.ext_sdk;
                }
                h5sdk07073cp.initConfig({
                    gamekey: res.d, //07073 开放平台游戏 KEY
                    uid: that.pf_params.uid, //07073 开放平台登录时 uid
                    debug: true, // 是否打印数据日志
                    onSubscribeCallback: function() {
                        that.g2b.postMessage(that.g2b.MESSAGES.FOCUS_RETURNSTATE, 1);
                    }, // 关注回调
                    onShareOkCallback: function() {
                        that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true);
                    }, // 分享回调
                    onIsSubscribeCallback: function() {
                        that.g2b.postMessage(that.g2b.MESSAGES.FOCUS_RETURNSTATE, 1);
                    }, // 是否关注回调
                });
                this.g2b.postMessage(this.g2b.MESSAGES.INIT_CALLBACK);
                console.log('ext= ' + ext_sdk);
            }

        })

    })


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
        console.log(JSON.stringify(res));
        var generate_order_id = res.d.order_id;
        var userId = res.d.userId;
        var readl_money = amount; // for test


        var url = "http://h5sdk.zytxgame.com/index.php/api/sign_order/" + that.passData.passId + "/" + that.passData.appId +
            "?ext_sdk=" + ext_sdk +
            "&ext_cp=" + generate_order_id + "&fee=" + readl_money + "&game_ordersn=" + generate_order_id +
            "&goods_id=" + readl_money + "&goods_name=" + param.goodsName + "&uid=" + that.pf_params.uid;

        this.g2b.getDataXHR(url, function(response) {

            if (response.c == 0) {
                console.log(response.d.str);
                var pay_obj = {
                    "gamekey": response.d.gamekey,
                    "ext_sdk": ext_sdk,
                    "ext_cp": generate_order_id,
                    "fee": readl_money,
                    "game_ordersn": generate_order_id,
                    "goods_id": readl_money,
                    "goods_name": 'money',
                    "time": response.d.time,
                    "uid": that.pf_params.uid,
                    "sign": response.d.sign,
                    "sign_type": "MD5",
                    "onPayCallback": function(res) {
                        console.log(res);
                    },
                    "onPayCancel": function(res) {
                        console.log(res);
                    },
                };


                h5sdk07073cp.pay(pay_obj);
            }

        });

    });
};
pf.prototype.checkFocus = function(data) {

    // var url = "http://h5sdk.zytxgame.com/index.php/api/focus/" + this.passData.passId + "?openid=" + data.openId;
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
        var url = "//h5sdk.zytxgame.com/index.php/api/login/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&level=" + level + "&power=" + power + "&currency=" + currency + "&cproleid=" + cproleid;

        this.g2b.getDataXHR(url, function(response) {
            console.log(JSON.stringify(response));
        });
        var baseData = {
            "role": this.pf_params.uid, //游戏角色的唯一ID
            "nickname": nickName, //游戏中角色的昵称，没有昵称的可以传空字符串
            "area": srvid, //游戏区标志
            "group": srvid //游戏服务器标志
        };
        var extendData = {
            "level": level, // 整型，默认为 0，当前等级
            "vipLevel": 0, // 整型，默认为 0，VIP 等级
            "score": data.power, // 整型，默认为 0，战力、综合评分等
            "isNew": 0, // 如果是创建角色后第一次登录为 1，默认为 0
            "relevel": "0", // 角色转生等级
        }
        h5sdk07073cp.gameReport('enterGame', baseData, extendData, function(){
            console.log("ok");
        })
    } else if (data.action == 'create_role') {
        var roleid = data.roleid;
        var srvid = data.srvid;
        var nickName = encodeURIComponent(data.rolename);

        var cproleid = data.cproleid;
        var url = "//h5sdk.zytxgame.com/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
        this.g2b.getDataXHR(url, function(response) {
            console.log(JSON.stringify(response));
        });
        var baseData = {
            "role": this.pf_params.uid, //游戏角色的唯一ID
            "nickname": nickName, //游戏中角色的昵称，没有昵称的可以传空字符串
            "area": srvid, //游戏区标志
            "group": srvid //游戏服务器标志
        };
        var extendData = {
            "level": level, // 整型，默认为 0，当前等级
            "vipLevel": 0, // 整型，默认为 0，VIP 等级
            "score": data.power, // 整型，默认为 0，战力、综合评分等
            "isNew": 1, // 如果是创建角色后第一次登录为 1，默认为 0
            "relevel": "0", // 角色转生等级
        }
        h5sdk07073cp.gameReport('enterGame', baseData, extendData, function(){
            console.log("ok");
        })
    } else if (data.action == 'enterCreate') {
        var roleid = data.roleid;
        var srvid = data.srvid;

        var url = "//h5sdk.zytxgame.com/index.php/api/sign_collect/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
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
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, true);
};
pf.prototype.showShare = function() {
    console.log('click share button');
    h5sdk07073cp.onShare({});
    // this.g2b.postMessage(this.g2b.MESSAGES.SHARE_CALLBACK, true);
}
pf.prototype.isDownloadable = function() {
    // this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, "http://img.h5sdk.zytxgame.com/img/android_apk/lcby/%E9%BE%99%E5%9F%8E%E9%9C%B8%E4%B8%9A.apk");
};

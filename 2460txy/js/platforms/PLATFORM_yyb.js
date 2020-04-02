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
    that.g2b.loadScript("http://qzonestyle.gtimg.cn/open/mobile/h5gamesdk/build/sdk.js ", function() {
        H5YSDK.ui.titleBar.hide(function(state) {
            console.log('sdk init');
            if (state == H5YSDK.STATE.SUCCESS) {
                //隐藏成功
            }
        });
    });
    that.g2b.postMessage(that.g2b.MESSAGES.INIT_CALLBACK);
};

pf.prototype.pay = function(amount, orderData) {
    // return;
    // console.log("amount " + amount);
    // console.log("orderData " + orderData);
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
    var search = this.g2b.object2search(param);
    this.g2b.createPay({
        search: search
    }, function(res) {
        var generate_order_id = res.d.order_id;

        var real_money = amount / 10;
        // var real_money = 1; //  for test

        var url = "http://h5sdk.zytxgame.com/index.php/api/sign_order/yyb/" + param.appId + "?openId=" + orderData.openId;
        //移动端才可支付
        // if (browser.versions.mobile || browser.versions.android || browser.versions.ios) {
        this.g2b.getDataXHR(url, function(response) {
            if (response.c == 0) {
                var args = {
                    saveValue: real_money,
                    zoneId: "1",
                    offerid: response.d.offerid,
                    openid: response.d.openid,
                    openkey: response.d.openkey,
                    pf: response.d.pf,
                    pfkey: response.d.pfkey,
                    onError: function(ret) {
                        // alert("error : " + ret.code, ret.msg);
                        // alert(JSON.stringify(ret));
                        alert('支付插件调起失败，请将应用宝更新至最新版本后再次尝试。');
                        closePayWindow();
                    },
                    onSuccess: function(ret) {
                        // alert("onSuccess : " + ret.code, ret.msg);
                        var notify = "http://h5sdk.zytxgame.com/index.php/api/notify/yyb?order_id=" + generate_order_id + '&ret=' + ret.code;
                        this.g2b.getDataXHR(notify, function() {

                        })
                        // closePayWindow();
                    }
                };
                // H5YSDK.requestPayForGood({
                // 	offerId: response.d.offerid,
                // 	openId: response.d.openid,
                // 	openKey: response.d.openkey,
                // 	sessionId: response.d.openid,
                // 	sessionType: response.d.openkey,
                // 	zoneId: '1',
                // 	pf: response.d.pf,
                // 	pfKey: response.d.pfkey,
                // 	goodsTokenUrl: "XXXX",
                // 	isCanChange: false,
                // 	unit: "张",
                // 	isShowSaveNum: true,
                // 	callback: function(code) {
                // 		if (code == yyb.PAY_STATE.SUCCESS) {
                // 			console.log('支付成功');
                // 		} else if (yyb.PAY_STATE.USERCANCEL) {
                // 			console.log('取消了支付');
                // 		} else {
                // 			console.log('支付失败');
                // 		}
                // 	}
                // });
                // H5YSDK.requestPay({
                // 	saveValue: real_money,
                // 	zoneId: "1",
                // 	offerid: response.d.offerid,
                // 	onError: function(ret) {
                // 		alert('error' + JSON.stringify(ret));
                // 		closePayWindow();
                // 	},
                // 	onSuccess: function(ret) {
                // 		var notify = "http://h5sdk.zytxgame.com/index.php/api/notify/yyb?order_id=" + generate_order_id + '&ret=' + ret.code;
                // 		this.g2b.getDataXHR(notify, function() {
                //
                // 		})
                // 	}
                // });

                // alert(JSON.stringify(args))
                // prompt("msg", JSON.stringify(args));

                H5YSDK.pay(args);

            }
        });
        // }


    });
};
pf.prototype.checkFocus = function(data) {
    this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, -1);
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

        var url = "//h5sdk.zytxgame.com/index.php/api/sign_collect/yyb/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
        this.g2b.getDataXHR(url, function(response) {});
    }
};
pf.prototype.logout = function() {};
pf.prototype.showQrCode = function() {

};
pf.prototype.isOpenShare = function() {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, false);
};
pf.prototype.isDownloadable = function() {
    this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, false);
};

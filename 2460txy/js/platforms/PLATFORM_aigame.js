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
	console.log('1');
	/*    this.g2b.loadScript("http://h5.play.cn/static/js/charge/egame-2.0.js", function() {
	        console.log('aigame SDK LOADED');
	        egame.init({
	            chargeUse: true,
	            userUse: true,
	            toobarUse: true,
	            share: {
	                summary: '爱游戏',
	                pic: 'http://a.jpg'
	            };
	        });
	        var init_url = 'http://h5sdk.zytxgame.com/index.php/api/init/aigame/' + that.passData.appId;
	        var login_id;
	        that.g2b.getDataXHR(init_url, function(response) {
	            if (response.c == 0) {
	                var client_id = response.d.client_id;
	                var service = response.d.service;
	                var redirect_uri = response.d.redirect_uri;
	                var token = response.d.token;
	                console.log('client_id:' + client_id);
	                console.log('service:' + service);
	                console.log('redirect_uri:' + redirect_uri);
	                console.log('token:' + token);
	                if (!login_id) {
	                    console.log('login_id:' + login_id);
	                    var param = {
	                        client_id: client_id,
	                        service: service,
	                        redirect_uri: redirect_uri,
	                    };
	                    app_callback: function(token) {
	                        var focus_url = 'http://h5sdk.zytxgame.com/index.php/api/focus/aigame/' + that.passData.appId + '?client_id=' + client_id + '&service=' + service + '&redirect_uri' + redirect_uri;
	                        that.g2b.getDataXHR(focus_url, function(response) {});
	                    };
	                    egame.userInit(param);
	                };
	            };
	        });
	    }); */
};

pf.prototype.pay = function(amount, orderData) {
	console.log("amount " + amount);
	console.log("orderData " + orderData);
	console.log(" goodsName " + orderData.subject);
	var param = {};
	param.openId = orderData.openId; // 2460 用户id
	param.openKey = orderData.openKey; // 2460 验证key
	param.appId = this.passData.appId; // 2460 游戏id
	param.money = amount; // 钱 单位分
	param.orderNo = orderData.orderNo; // 研发游戏订单id
	param.ext = orderData.ext || ""; // serverid
	param.data = orderData.actor_id; //  put actor_id into data
	param.goodsName = orderData.subject;
  param.cproleid = orderData.cproleid; // 商品名
	var search = this.g2b.object2search(param);
	this.g2b.createPay({
		search: search
	}, function(res) {
		var generate_order_id = res.d.order_id;

		var real_money = amount;
		// var real_money = 1; //  for test
		// var sign = hex_md5(generate_order_id+real_money+param.goodsName+channel+param.openKey);
		var url = "http://h5sdk.zytxgame.com/index.php/api/sign_order/aigame/" + param.appId + "?orderid=" + generate_order_id +
			"&money=" + real_money +
			"&product=" + param.goodsName +
			"&channel=" + channel
		// +"sign=" + sign
		;
		console.log(url);
		// alert("url" + url);
		this.g2b.getDataXHR(url, function(response) {
			// alert("response" + JSON.stringify(response));
			console.log(JSON.stringify(response));
			if (response.c == 0) {
				var goodsName = param.goodsName;
				var purchaseData = {
					orderid: generate_order_id,
					money: real_money,
					product: goodsName,
					channel: channel,
					sign: response.d.sign,
					attach: generate_order_id,
					onPayCallback: function(data) {
						if (data.status == 1) {
							alert("支付成功");
						} else {
							alert("支付失败");
						}
					},
					onPayCancel: function() {
						alert("支付取消");
					}
				};
				purchaseData = JSON.stringify(purchaseData);

				// bbweiyou.pay(purchaseData);
				// console.log("success");
				// alert("purchaseData " + purchaseData);
				// console.log(purchaseData);
				// alert(purchaseData);
				bbweiyou.pay(purchaseData);
				// alert("pay end " + bbweiyou);
			}
		});



		closePayWindow();

	});
};

pf.prototype.checkFocus = function(data) {

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

		var url = "//h5sdk.zytxgame.com/index.php/api/sign_collect/aigame/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
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

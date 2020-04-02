var p_name;
var recharge;
var channel;
var gameid;
var user_id;
var nickname;
var pfGameId;
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
	var that = this;
	channel = this.pf_params.channel;
	gameid = this.pf_params.gameid;
	user_id = this.pf_params.openid;
	nickname = this.pf_params.nickname;
	pfGameId = this.pf_params.passId;
	this.g2b.getDataXHR('//'+location.host+'/index.php/api/focus/youdao/' + this.passData.appId, function(response) {
		if (response.c == 0) {
			this.g2b.loadScript('http://wx.game.idian.cn/js/module/cps/smp-sdk-min.js', function() {
				var sdk = window.SMP_SDK;

				this.g2b.postMessage(this.g2b.MESSAGES.INIT_CALLBACK);
			});
		}



	});
};

pf.prototype.pay = function(amount, orderData) {
	console.log("amount " + amount);
	console.log("orderData " + orderData);
	var param = {};
	var that = this;
	param.openId = orderData.openId;
	param.openKey = orderData.openKey;
	param.appId = this.passData.appId;
	param.money = amount;
	param.orderNo = orderData.orderNo;
	param.ext = orderData.ext || "";
	param.data = orderData.actor_id; //  put actor_id into data
	param.goodsName = orderData.subject;
  param.cproleid = orderData.cproleid;
	param.platform = p_name;
	var search = this.g2b.object2search(param);
	this.g2b.createPay({
		search: search
	}, function(res) {
		var generate_order_id = res.d.order_id;
		var userId = res.d.userId;
		var ext = generate_order_id;
		var readl_money = amount / 100;
		// var readl_money = 1 / 100; // for test
		var url = "http://"+location.host+"/index.php/api/sign_order/" + passId + "/" + param.appId;
		this.g2b.getDataXHR(url, function(response) {
			if (response.c == 0) {
				var pay_data = {
					identify: response.d.identify_id,
					token: response.d.token,
					channel: channel,
					amount: readl_money,
					callback: "http://"+location.host+"/index.php/api/notify/" + passId + "/" + pfGameId,
					order_id: generate_order_id,
					type: "1",
					game_id: gameid,
					goods_id: readl_money,
					nickname: nickname,
					openid: user_id
				};
				console.log(JSON.stringify(pay_data));
				sdk.pay(pay_data);
				sdk.config(function(args) {
					//支付回调处理方法，也可以通过这个方法用来测试调用的返回信息
					// alert(args.errMsg);
				})

				closePayWindow();
			}
		});
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
        var url = "//"+location.host+"/index.php/api/login/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&level=" + level + "&power=" + power + "&currency=" + currency + "&cproleid=" + cproleid;

		this.g2b.getDataXHR(url, function(response) {});
	} else if (data.action == 'create_role') {
		var roleid = data.roleid;
		var srvid = data.srvid;
		var nickName = encodeURIComponent(data.rolename);
		var cproleid = data.cproleid;
        var url = "//"+location.host+"/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
		this.g2b.getDataXHR(url, function(response) {});
	} else if (data.action == 'enterCreate') {
		var roleid = data.roleid;
		var srvid = data.srvid;
		var url = "//"+location.host+"/index.php/api/sign_collect/" + passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
		this.g2b.getDataXHR(url, function(response) {});
	}
};
pf.prototype.showQrCode = function() {};


pf.prototype.isDownloadable = function() {
	this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, false);
};
pf.prototype.isOpenShare = function() {
	this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, false);
};

var u;
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
	console.log(this.passData);
	var that = this;
	this.g2b.getDataXHR("http://api.baizegame.com/index.php/api/focus/" + passId + "/" + this.passData.appId, function(response) {
		if (response.c == 0) {
			this.g2b.loadScript('https://cdn.hortor.net/sdk/sdk_agent.min.js', function() {
				var sdk = window.HORTOR_AGENT;
				sdk.init();
				sdk.config({
					gameId: response.d,
					share: {
						timeline: {
							title: "活着走出去",
							imgUrl: "http://img.2460.xileyougame.com/img/icon/tldk/346-316.jpg",
							success: function() {
								that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true);
							},
							cancel: function() {
								that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, false);
							}
						},
						friend: {
							title: "活着走出去",
							desc: "活着走出去",
							imgUrl: "http://img.2460.xileyougame.com/img/icon/tldk/346-316.jpg",
							success: function() {
								that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, true);
							},
							cancel: function() {
								that.g2b.postMessage(that.g2b.MESSAGES.SHARE_CALLBACK, false);
							}
						},
						shareCustomParam: { //配置 定义参数
							cp_param1: '', // 定义参数key必须以cp_开始
							cp_param2: "",
						}
					},
					pay: {
						success: '',
						cancel: ''
					}
				});
				this.g2b.postMessage(this.g2b.MESSAGES.INIT_CALLBACK);
			});
		}
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
	param.platform = 'hortor';
	var search = this.g2b.object2search(param);
	this.g2b.createPay({
		search: search
	}, function(res) {
		var generate_order_id = res.d.order_id;
		var user_id = res.d.userId;
		var readl_money = amount;
		// var readl_money = 1;
		var subject = orderData.subject;
		this.g2b.getDataXHR("http://api.baizegame.com/index.php/api/sign_order/" + passId + "/" + param.appId + "?amount=" + readl_money + '&server=' + param.ext + '&uid=' + user_id + '&order_id=' + generate_order_id, function(response) {
			if (response.c == 0) {
				var sdk = window.HORTOR_AGENT;
				sdk.pay({
					'order_id': response.d.order_id,
					'app_id': response.d.appid,
					'timestamp': response.d.time,
					'nonce_str': '',
					'package': '',
					'sign_type': '',
					'pay_sign': ''
				});
				closePayWindow();
			}
		})

	});
};
pf.prototype.checkFocus = function(data) {
	this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, -1);
};
pf.prototype.reportData = function(data) {

	if (data.action == 'enterGame') {
		var roleid = data.roleid;
		var srvid = data.srvid;
		// var nickName = encodeURIComponent(data.rolename);
		var nickName = data.rolename;
		var level = data.rolelevel;
		var power = data.power;
        var currency = data.currency;
        var cproleid = data.cproleid;
        var url = "//api.baizegame.com/index.php/api/login/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&level=" + level + "&power=" + power + "&currency=" + currency + "&cproleid=" + cproleid;


		this.g2b.getDataXHR(url, function(response) {});
	} else if (data.action == 'create_role') {
		var roleid = data.roleid;
		var srvid = data.srvid;
		// var nickName = encodeURIComponent(data.rolename);
		var nickName = data.rolename;
		var cproleid = data.cproleid;
        var url = "//api.baizegame.com/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
		this.g2b.getDataXHR(url, function(response) {});
	} else if (data.action == 'enterCreate') {
		var roleid = data.roleid;
		var srvid = data.srvid;
		var url = "//api.baizegame.com/index.php/api/sign_collect/hortor/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
		this.g2b.getDataXHR(url, function(response) {});
	} else if (data.action == 'isSubscribe'){
		return this.pf_params.isSubscribe ==="false" ? false : true;
	}
};
pf.prototype.logout = function() {};
pf.prototype.showQrCode = function() {
	console.log("pf showQrCode called");

};
pf.prototype.isOpenShare = function() {
	this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, true);
};
pf.prototype.isDownloadable = function() {
	this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, false);
};

// pf.prototype.showShare = function() {
// this.g2b.postMessage(this.g2b.MESSAGES.SHARE_CALLBACK, false);
// }

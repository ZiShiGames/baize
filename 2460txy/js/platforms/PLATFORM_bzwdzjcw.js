var u;
var user_level;
var _gameIframe;
var pf = function (g2b, shareInfo, pf_params, passData) {
	this.g2b = g2b;
	this.shareInfo = shareInfo;
	this.pf_params = pf_params;

	this.passData = passData;
	p_name = passData.passId;
	this.reyunurl = "";
	this.init();
};

pf.prototype = new platform();

pf.prototype.init = function () {
	console.log(this.passData);
	//	console.log('bz成功：',gowanme_param);
	var that = this;
	this.g2b.getDataXHR("//api.baizegame.com/index.php/api/focus/" + passId + "/" + this.passData.appId, function (response) {
		console.log('分享', that.shareInfo);
		//	     kkkSDK.shareToArk({
		//	    	 summary:that.shareInfo.desc,
		//	    	 picUrl:that.shareInfo.imgUrl,
		////	    	 ​extendInfo:that.shareInfo,
		//	     	 }).then(function(res){
		//	     		 	console.log('分享:',res)
		//	        });
		// && user_level >= 60
		if (that.pf_params.is_activity == 1) {
			_gameIframe = document.getElementById("gameFrame");
			var _data = {};
			_data.identify = "sdw";
			_data.msg = "activity";
			_data.data = true;
			_gameIframe.contentWindow.postMessage(_data, "*")

		}
		this.g2b.postMessage(this.g2b.MESSAGES.INIT_CALLBACK);
	});
};

pf.prototype.pay = function (amount, orderData) {
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
	param.platform = 'bzwdzjcw';
	var search = this.g2b.object2search(param);
	this.g2b.createPay({
		search: search
	}, function (res) {
		var generate_order_id = res.d.order_id;
		var user_id = res.d.userId;
		var readl_money = amount;
		// var readl_money = 1;
		var subject = orderData.subject;
		this.g2b.getDataXHR("//api.baizegame.com/index.php/api/sign_order/" + passId + "/" + param.appId + "?amount=" + readl_money + '&server=' + param.ext + '&uid=' + user_id + '&order_id=' + generate_order_id, function (response) {
			if (response.c == 0) {
				var sdk = window.kkkSDK;
				//				response.d.parms.serverId=response.d.parms.serverId.toString();
				console.log('充值：', response.d)
				sdk.recharge(response.d).then(function (res) {
					console.log('充值：', res)
				});
				closePayWindow();
			}
		})

	});
};
pf.prototype.checkFocus = function (data) {
	this.g2b.postMessage(this.g2b.MESSAGES.FOCUS_RETURNSTATE, -1);
};
pf.prototype.reportData = function (data) {

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
		this.g2b.getDataXHR(url, function (response) {});
		console.log('切换角色登录：', data);
		user_level = level;
		kkkSDK.changeRole({
			roleId: data.roleid,
			roleName: data.rolename,
			serverName: data.srvid,
			serverId: data.srvid,
			roleLevel: data.rolelevel,
			userMoney: "",
			vipLevel: 0,
		}).then(function (res) {
			console.log('切换角色登录：', res)
		});

	} else if (data.action == 'create_role') {
		var roleid = data.roleid;
		var srvid = data.srvid;
		// var nickName = encodeURIComponent(data.rolename);
		var nickName = data.rolename;
		var cproleid = data.cproleid;
		var url = "//api.baizegame.com/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
		this.g2b.getDataXHR(url, function (response) {});
		//		console.log('角色创建：', data)
		kkkSDK.createRole({
			roleId: roleid,
			roleName: nickName,
			serverName: srvid,
			serverId: srvid,
			roleLevel: data.rolelevel,
			userMoney: "",
			vipLevel: 0,
		}).then(function (res) {
			console.log('角色创建：', res)
		});

	} else if (data.action == 'enterCreate') {
		var roleid = data.roleid;
		var srvid = data.srvid;
		var url = "//api.baizegame.com/index.php/api/sign_collect/hortor/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
		this.g2b.getDataXHR(url, function (response) {});

	} else if (data.action == 'isSubscribe') {
		return this.pf_params.isSubscribe === "false" ? false : true;
	} else if (data.action == 'isfollows') { //关注开关
		return true;
	} else if (data.action == 'isshares') { //分享开关
		return true;
	} else if (data.action == 'is_activity') {
		console.log('is_activity', gowanme_param);
		if (gowanme_param['ext']['is_activity'] && gowanme_param['ext']['is_activity'] > 0) {
			return 1;
		} else {
			return 0;
		}
	} else if (data.action == 'activity') {
		//		var roleid = data.roleid;
		//		var srvid = data.srvid;
		//		// var nickName = encodeURIComponent(data.rolename);
		//		var nickName = data.rolename;
		//		var cproleid = data.cproleid;

		kkkSDK.activity({
			target: 'bindAliPay',
			options: {
				//			roleId:roleid,
				// 			roleName:nickName,
				//			serverName:srvid,
				//			serverId:srvid,
				roleLevel: data.rolelevel
			}
		}).then(function (res) {
			console.log('活动接口:', res)
			return res.statusCode;
		});
	}
};
pf.prototype.logout = function () {};
pf.prototype.showQrCode = function () {
	console.log("pf showQrCode called");

};
pf.prototype.isOpenShare = function () {
	this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, true); //分享开关
};
pf.prototype.isDownloadable = function () {
	this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, false);
};

// pf.prototype.showShare = function() {
// this.g2b.postMessage(this.g2b.MESSAGES.SHARE_CALLBACK, false);
// }
window.addEventListener("message", function (e) {
	var _data = e.data;
	var that = this;
	console.log(_data);
	if (_data.identify == "sdw") {
		switch (_data.msg) {
			case "runActivity":
				kkkSDK.activity({
					target: 'bindAliPay',
					options: {
						roleLevel: _data.rolelevel
					}
				}).then(function (res) {
					console.log('活动接口:', res)
					if (res.statusCode == 0) {
						_gameIframe = document.getElementById("gameFrame");
						var _data = {};
						_data.identify = "sdw";
						_data.msg = "bindOk";
						_data.data = true;
						_gameIframe.contentWindow.postMessage(_data, "*")
					}
				});
				break;
			default:
				break
		}
	}
});
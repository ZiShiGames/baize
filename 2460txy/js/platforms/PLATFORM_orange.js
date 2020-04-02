var p_name;
var recharge;
var p_appkey;
var appid;
var responsekey;
var reportkey;
var sdkInit;
var channel_id;
var uid;
var srvid;
var title = '一起来玩龙城霸业';
var share_img = 'http://img.2460.xileyougame.com/img/login/feiwanba/loading.jpg';
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
	this.g2b.getDataXHR("http://h5sdk.zytxgame.com/index.php/api/focus/" + p_name + "/" + this.passData.appId, function(response) {
		this.g2b.loadScript("http://h5sdk.zytxgame.com/js/jquery-3.1.1.js", function() {
			this.g2b.loadScript('http://yx.qieyx.com/channelsdk/sbpulsdk.js?v=1209', function() {
				console.log(response.d.user_data);
				if (response.c == 0) {
					SbPulSdk.init(JSON.parse(response.d.user_data), function(channelSdk) {
						channel_id = channelSdk.channelId;
						if (channelSdk.channelId == 205) {
							channelSdk.shareConfig({
								"timeline": {
									"title": title,
									"imgUrl": share_img,
									"success": function() {
										this.g2b.postMessage(this.g2b.MESSAGES.SHARE_CALLBACK, true);
									},
									"cancel": function() {}
								},
								"friend": {
									"title": title,
									"desc": title,
									"imgUrl": share_img,
									"success": function() {
										this.g2b.postMessage(this.g2b.MESSAGES.SHARE_CALLBACK, true);
									},
									"cancel": function() {}
								},
								"shareCustomParam": { //配置自定义参
									"cp_test": 1, //自定义参数key必须cp_开始
									"cp_test2": 2
								}
							});
						}
						if (channelSdk.channelId == 205) {
							channelSdk.follow();
						}

						if (channelSdk.channelId == 244) {
							channelSdk.shareConfig({
									"shareSuccessBack": function(type) {
										this.g2b.postMessage(this.g2b.MESSAGES.SHARE_CALLBACK, true);
									}
								},
								//cp自定义参数，如果不需要可以留留空
								{
									"cp_zoneid": 1,
									"cp_service": 2
								});

							//关注， 点击游戏里面关注按钮的时候调用下面方法
							channelSdk.follow();
							//邀请，点击游戏里面邀请的时候调用下面方法
							channelSdk.invite();
						}
						if (channelSdk.channelId == 260) {
							$('#share').click(function() {
								channelSdk.share(function(rsp) {
									if (rsp.result == 0) {
										this.g2b.postMessage(this.g2b.MESSAGES.SHARE_CALLBACK, true);
									}
								});
								//rsp
								//rsp.result  0分享成功，-1取消， -2 失败
								//rsp.msg 传回的提示信息
							});
							$('#getFriends').click(function() {
								channelSdk.getFriends(function(rsp) {
									alert(JSON.stringify(rsp))
								});
								//rsp
								//rsp.result  为0获取成功，否则失败
								//rsp.msg 传回的提示信息
								//rsp.friends 好友的uid数组列表
							});



							if (channelSdk.loginInfo.loginType == 2) {
								$('#switchAccount').click(function() {
									channelSdk.loginOut();
								});
							}
						}







					});
				}

				this.g2b.postMessage(this.g2b.MESSAGES.INIT_CALLBACK);

			});

		});
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
		var sign_url = "http://h5sdk.zytxgame.com/index.php/api/sign_order/" + p_name + "/" + param.appId + "?order=" + generate_order_id +
			"&goodsname=" + param.goodsName + "&fee=" + readl_money + "&ext=" + ext;
		this.g2b.getDataXHR(sign_url, function(response) {
			if (response.c == 0) {
				var cpPayParams = {
					order: generate_order_id,
					cpgameid: response.d.cpgameid,
					qqesuid: response.d.qqesuid,
					channelid: response.d.channelid,
					channeluid: response.d.channeluid,
					cpguid: response.d.cpguid,
					goodsname: param.goodsName,
					fee: readl_money,
					ext: response.d.ext,
					timestamp: response.d.time,
					sign: response.d.sign
				};
				console.log(JSON.stringify(cpPayParams));
				SbPulSdk.pay(cpPayParams);
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
		srvid = data.srvid;
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
		if (channelSdk.channelId == 225) {
			/**
			 * 参数传入角色所在区服id
			 */
			channelSdk.createRole(srvid);
		}
		var cproleid = data.cproleid;
        var url = "//h5sdk.zytxgame.com/index.php/api/create_role/" + this.passData.passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid + "&nickname=" + nickName + "&cproleid=" + cproleid;
		this.g2b.getDataXHR(url, function(response) {});
	} else if (data.action == 'enterCreate') {
		var roleid = data.roleid;
		var srvid = data.srvid;

		var url = "/index.php/api/sign_collect/" + passId + "/" + this.passData.appId + "?roleid=" + roleid + "&srvid=" + srvid;
		this.g2b.getDataXHR(url, function(response) {});
	}
};
pf.prototype.showQrCode = function() {};


pf.prototype.isDownloadable = function() {
	if (channel_id == 260) {
		this.g2b.postMessage(this.g2b.MESSAGES.RETURNDOWNLOAD, false);
	}

};
pf.prototype.isOpenShare = function() {
	this.g2b.postMessage(this.g2b.MESSAGES.RETURNSHARE, false);
};

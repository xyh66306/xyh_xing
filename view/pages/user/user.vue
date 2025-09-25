<template>
	<view class="page">
<!-- 
		<view class="invite u-border" v-if="open_team" @click="showInvite">
			<u-icon name="gift" color="primary" size="24"></u-icon>
			<view style="margin-left: 20rpx;">邀请好友</view>
		</view> -->
		<template v-if="open_company">
			<view class="head">
				<view class="user">
					<view class="uinfo">
						<u-avatar :text="user.nickname.substr(-1)" randomBgColor color-index="18" size="50"></u-avatar>
						<view class="username" @click="openTeam">
							<view>{{user.nickname}} (ID: {{user.id}})</view>
							<view style="margin-top: 10rpx;">
								<u-tag text="账户安全保障中" size="mini" icon="integral-fill" plain type="warning"></u-tag>
							</view>
						</view>
					</view>
				</view>
			</view>
			<view class="navs">
				<view class="h3">公司管理</view>
				<u-cell-group>
					<u-cell title="承兑商用户" isLink url="/pages/company/user/list"></u-cell>
					<u-cell title="承兑商团队" isLink url="/pages/company/group/list"></u-cell>
				</u-cell-group>
			</view>
			
			<view class="navs">
				<view class="h3">返佣管理</view>
				<u-cell-group>
					<u-cell title="团队返佣订单" isLink url="/pages/userrebate/yongjin"></u-cell>
					<u-cell title="返佣日志" isLink url="/pages/company/group/list"></u-cell>
					<u-cell title="返佣设置" isLink url="/pages/userrebate/comsetting"></u-cell>
				</u-cell-group>
			</view>
			
			<view class="navs">
				<view class="h3">安全设置</view>
				<u-cell-group>
					<u-cell title="修改密码" isLink url="/pages/changepwd/changepwd"></u-cell>
					<u-cell title="修改支付密码" isLink url="/pages/changepaypwd/changepaypwd"></u-cell>
					<u-cell title="谷歌认证" isLink url="/pages/google/google" :value="user.google_secret ? '已认证' : '未认证'"></u-cell>
				</u-cell-group>
			</view>
			
		</template>
		<template v-else>
		<view class="head">
			<view class="user">
				<view class="uinfo" @click="openTeam">
					<u-avatar :text="user.nickname.substr(-1)" randomBgColor color-index="18" size="50"></u-avatar>
					<view class="username">
						<view>{{user.nickname}} (ID: {{user.id}})</view>
						<view style="margin-top: 10rpx;">
							<u-tag text="账户安全保障中" size="mini" icon="integral-fill" plain type="warning"></u-tag>
						</view>
					</view>
				</view>
				<u-image src="/static/qrcode.png" width="20" height="20" style="margin-left: auto;" @click="showInvite"></u-image>
			</view>
		</view>
		<view class="navs">
			<u-cell-group>
				<u-cell title="兑出记录" isLink url="/pages/buy_order/buy_order"></u-cell>
				<u-cell title="兑入记录" isLink url="/pages/sale_order/sale_order"></u-cell>
				<u-cell title="充值记录" isLink url="/pages/recharge/recharge"></u-cell>
				<u-cell title="提币记录" isLink url="/pages/withdraw/withdraw"></u-cell>
				<u-cell title="转账记录" isLink url="/pages/moneylog/transfer"></u-cell>
				<u-cell title="资产流水" isLink url="/pages/moneylog/moneylog"></u-cell>
				<u-cell title="推荐列表" isLink url="/pages/team/list" v-if="open_team && !open_company"></u-cell>
				<u-cell title="佣金流水" isLink url="/pages/userrebate/yongjin" v-if="open_team"></u-cell>
			</u-cell-group>
		</view>
		
		<view class="navs">
			<u-cell-group>
				<u-cell title="收款设置" isLink url="/pages/card/card"></u-cell>
				<u-cell title="返佣设置" isLink url="/pages/userrebate/setting" v-if="open_team"></u-cell>
				<!-- <u-cell title="修改密码" isLink url="/pages/changepwd/changepwd"></u-cell> -->
				<u-cell title="修改支付密码" isLink url="/pages/changepaypwd/changepaypwd"></u-cell>
				<u-cell title="身份认证" isLink url="/pages/auth/auth" :value="user.sfz_status==1?'已认证' : user.sfz_status==2?'待审核':'未认证'"></u-cell>
				<u-cell title="谷歌认证" isLink url="/pages/google/google"
					:value="user.google_secret ? '已认证' : '未认证'"></u-cell>
			</u-cell-group>
		</view>
		</template>
		<view class="logout">
			<u-button type="error" shape="circle" @click="logout">退出登录</u-button>
		</view>
		<u-popup :show="show" @close="closeInvite" mode="center" :round="10">
			<view class="box">
				<view style="margin-bottom: 30rpx;">扫一扫二维码，注册新用户</view>
				<!-- <u-image :src="invite.qr" width="200" height="200"></u-image> -->
				<yy-qrcode :text="invite.url" />
				<u-button type="primary" plain style="margin-top: 30rpx;" @click="copyUrl">复制分享链接</u-button>
			</view>
		</u-popup>
	
	</view>
</template>

<script>
	export default {
		data() {
			return {
				user: {},
				open_team: false,
				times: 0,
				timer: null,
				show: false,
				showyj:false,	//是否显示佣金流水
				yjNums:0,		//佣金流水点击次数
				invite: {},
				open_company:false,
			}
		},
		onLoad() {
			this.getInvite();
		},
		onShow() {
			// this.user = uni.getStorageSync('user') || {};
			// this.open_team  = true
			// this.open_team = uni.getStorageSync('open_team') ? true : false;
			this.open_company = uni.getStorageSync('user_group')==2 ? true : false;
			this.getUserInfo();
		},
		methods: {
			getUserInfo(){
				uni.$u.http.post('/api/user/getUserinfo').then(res => {
					if(res.code == 1) {
						this.user =res.data;
					}
				})
			},
			logout() {
				uni.showModal({
					content: '确定要退出当前账号吗',
					success: (res) => {
						uni.removeStorageSync('token');
						if (res.confirm) {
							uni.redirectTo({
								url: '/pages/login/login'
							})
						}
					}
				});
			},
			openTeam() {
				console.log(1);
				if (this.timer) {
					clearTimeout(this.timer);
				}
				
				console.log(this.times);
				
				this.times++;
				if (this.times >= 5) {
					this.open_team = !this.open_team;
					uni.setStorageSync('open_team', this.open_team);
					uni.$u.toast(this.open_team ? '已开启团队模式' : '已关闭团队模式');
					this.times = 0;
					return;
				}
				this.timer = setTimeout(() => {
					this.times = 0;
				}, 1000);
			},
			showInvite() {
				
				this.show = true;
			},
			closeInvite() {
				this.show = false;
			},
			getInvite() {
				uni.$u.http.post('/api/index/getInvite').then(res => {
					if(res.code == 1) {
						this.invite = res.data;
						console.log(res.data.url)
					}
				})
			},
			copyUrl() {
				uni.setClipboardData({
					data: this.invite.url,
					success: () => {
						this.show = false;
						uni.$u.toast('复制成功');
					}
				})
			}
		}
	}
</script>

<style lang="scss">
	.page {
		padding: 30rpx;
	}

	.head {}

	.user {
		display: flex;
		align-items: center;
	}
	.uinfo {
		display: flex;
		align-items: center;
	}

	.username {
		margin-left: 30rpx;
	}

	.navs {
		margin-top: 30rpx;
		background-color: #fff;
		border-radius: 10rpx;
		overflow: hidden;
		.h3 {
			font-size:32rpx;
			font-weight: bold;
			padding:20rpx 0rpx 20rpx 20rpx;
		}
	}

	.logout {
		margin-top: 30rpx;
		padding: 30rpx 0;
	}

	.invite {
		margin-top: 30rpx;
		padding: 20rpx 30rpx;
		background-color: $u-primary-light;
		color: $u-primary;
		font-size: 18px;
		display: flex;
		align-items: center;
		justify-content: center;
		border-radius: 20rpx;

		border-color: $u-primary-disabled !important;
	}
	
	.box {
		width: 80vw;
		padding: 60rpx 80rpx;
		display: flex;
		flex-direction: column;
		align-items: center;
	}
</style>
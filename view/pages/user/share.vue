<template>
	<view>
		<view class="wrap">
			<ikun-qrcode width="500" height="500" unit="rpx" color="#000000" :data="invite.url" />
		</view>
		<view class="wrap u-p-30">
			<u-button type="primary" @click="copyUrl">点击复制网址</u-button>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				invite: {
					url: '',
				},
			}
		},
		onLoad() {
			this.getInvite();
		},
		methods: {
			getInvite() {
				uni.$u.http.post('/api/index/getInvite').then(res => {
					if (res.code == 1) {
						this.invite = res.data;
					}
				})
			},
			copyUrl() {
				uni.setClipboardData({
					data: this.invite.url,
					success: () => {
						this.show = false;
						uni.$u.toast("已复制网�");
					}
				})
			}
		}
	}
</script>

<style>
	.wrap {
		width: 500rpx;
		height: 500rpx;
		margin: 50rpx auto;
	}
</style>
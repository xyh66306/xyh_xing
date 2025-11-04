<template>
	<view>
		<view class="wrap">
			<ikun-qrcode
			    width="500"
			    height="500"
			    unit="rpx" 
			    color="#000000"
			    :data="invite.url" 
			/>
		</view>
	</view>
</template>

<script>
	export default {
		data(){
			return {				
				invite: {
					url:'',
				},
			}
		},
		onLoad() {
			this.getInvite();
		},
		methods:{
			getInvite() {
				uni.$u.http.post('/api/index/getInvite').then(res => {
					if(res.code == 1) {
						this.invite = res.data;
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

<style>
	.wrap {
		width: 500rpx;
		height: 500rpx;
		margin: 50rpx auto;
	}
</style>
<template>
	<view class="page">
		<template v-if="list.length>0">
			<view class="list" v-for="(vo,index) in list" :key="index">
				<view class="item u-border-bottom" @click="gotourl(vo.id)">
					<view class="row">
						<view class="money">{{vo.num}}</view>
						<view class="u-info time">{{vo.createtime}}</view>
					</view>
					<view class="row">
						<view class="addr"></view>
						<view class="u-info fee">手续费 {{vo.fee}}</view>
					</view>
					<view class="row">
						<view class="u-warning state" v-if="vo.status=='normal'"> 已审核 </view>
						<view class="u-warning" v-else> 未审核 </view>
						<view class="u-info time"></view>
					</view>
				</view>
			</view>
		</template>
		<template v-else>
			<view class="empty">
				<u-empty mode="history" icon="/static/order.png"></u-empty>
			</view>
		</template>

	</view>
</template>

<script>
	export default {
		data() {
			return {
				list: [],
				show: false,
			}
		},
		onLoad() {
			this.getlst();
		},
		methods: {
			gotourl(id) {
				uni.navigateTo({
					url: '/pages/withdraw/details?id=' + id
				})
			},
			getlst() {
				uni.$u.http.post('/api/withdraw/index').then(res => {
					if (res.code == 1) {
						this.list = res.data;
					}
				})
			},
			close() {
				this.show = false;
			},
			open() {
				this.show = true;
			},
			submit() {
				this.$refs.addForm.validate().then(res => {
					uni.$u.toast('提交成功');
					this.show = false;
				})
			}
		},
		onNavigationBarButtonTap() {
			this.show = true;
		}
	}
</script>

<style lang="scss">
	.item {
		padding: 20rpx 30rpx;
		font-size: 14px;
	}

	.row {
		display: flex;
		align-items: center;
	}

	.money {
		font-size: 20px;
		flex: 1;
	}

	.addr {
		flex: 1;
		font-size: 12px;
	}

	.row+.row {
		margin-top: 10rpx;
	}

	.state {
		flex: 1;
	}

	.form {
		padding: 30rpx;
	}

	.tip {
		padding: 30rpx 0;
		font-size: 14px;
	}

	.add {
		padding-bottom: 30rpx;
	}
</style>
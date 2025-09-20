<template>
   <div :id="containerId"></div>
   <!--这个组件渲染出一个具有指定 id 的 div 标签，用于展示生成的二维码-->
</template>

<script>
import QRCode from 'qrcodejs2'; //导入 qrcodejs2 库

export default {
   name: 'QrCode', //组件名称
   props: {
      text: {
         //接收二维码文本信息
         type: String, //类型为字符串
         required: true //必传参数
      },
      containerId: {
         //生成的二维码容器的 id 值，用于在 DOM 中查找该标签
         type: String, //类型为字符串
         default: 'qrcode-container' //默认值为 'qrcode-container'
      },
      size: {
         //二维码尺寸，宽高相等
         type: Number, //类型为数字
         default: 200 //默认值为 200
      },
      fgColor: {
         //二维码颜色
         type: String, //类型为字符串
         default: '#000000' //默认值为黑色
      },
      bgColor: {
         //二维码背景色
         type: String, //类型为字符串
         default: '#ffffff' //默认值为白色
      },
      correctLevel: {
         //二维码识别错误等级
         type: Number, //类型为数字
         default: QRCode.CorrectLevel.H //默认值为最高级别
      }
   },
   mounted() {
      //组件挂载时执行的函数
      this.generateQRCode(); //生成二维码
   },
   methods: {
      generateQRCode() {
         //生成二维码的方法
         new QRCode(this.containerId, {
            //创建 qrcodejs2 实例
            text: this.text, //二维码文本信息
            width: this.size, //二维码尺寸
            height: this.size,
            colorDark: this.fgColor, //二维码颜色
            colorLight: this.bgColor, //二维码背景色
            correctLevel: this.correctLevel //二维码识别错误等级
         });
      }
   }
};
</script>

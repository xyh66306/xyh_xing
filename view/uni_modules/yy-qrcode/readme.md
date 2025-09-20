# yy-qrcode 组件文档

## 简介

`yy-qrcode` 是一个基于 [qrcodejs2](https://github.com/davidshimjs/qrcodejs) 的 Vue 组件，用于生成二维码。

## 安装

通过 npm 安装：

```bash
npm install qrcodejs2 
```

## 特点
- yy-qrcode遵循easycom规范 (opens new window)，无需注册即可使用。

## 引入组件

在需要使用组件的地方引入 `yy-qrcode` 组件：

```vue
<template>
   <div>
      <yy-qrcode :text="qrcodeText" />
   </div>
</template>

<script>


export default {
 
   data() {
      return {
         qrcodeText: 'https://www.example.com'
      };
   }
};
</script>
```

## 使用

### Props

|名称|类型|必须|默认值|说明|
|---|---|---|---|---|
|text|String|是| |要转换的文本或链接|
|containerId|String|否|'qrcode-container'|二维码容器的 ID，用于在组件内部生成二维码|
|size|Number|否|200|生成的二维码图片尺寸|
|fgColor|String|否|#000000|二维码前景色|
|bgColor|String|否|#ffffff|二维码背景色|
|correctLevel|Number|否|QRCode.CorrectLevel.H|修正级别，可选值为 L、M、Q、H|

### 示例

```vue
<template>
   <div>
      <yy-qrcode 
         :text="qrcodeText" 
         :size="300" 
         fg-color="#ff0000" 
         bg-color="#eeeeee" 
         correct-level="M" 
      />
   </div>
</template>

<script>


export default {

   data() {
      return {
         qrcodeText: 'https://www.example.com'
      };
   }
};
</script>
```

## 不懂的可以加我v（a634691481__0）聊

MIT License.
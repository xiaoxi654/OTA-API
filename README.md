# OTA-API
A generic API to get Android ROM

### 调用方法

```GET /<device>/<rom>/[lastest|check]```

其中 `<device>` 为设备代号

`<rom>` 为 ROM 类型，暂时只支持 AICP ( `aicp` ) 、Lineage OS ( `los` ) 、魔趣 ( `mokee` )

`lastest|check` 参数可选，若不加则返回从 API 抓取到的所有数据，`lastest` 只返回最新的一条，`check` 检查该机型对应 ROM 是否可用

### TODO

* LOS 使用正则从网页抓取更详细的数据

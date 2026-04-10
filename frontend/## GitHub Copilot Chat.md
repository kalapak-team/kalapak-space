## GitHub Copilot Chat

- Extension: 0.42.3 (prod)
- VS Code: 1.114.0 (e7fb5e96c0730b9deb70b33781f98e2f35975036)
- OS: win32 10.0.26200 x64
- GitHub Account: ksmfanoldsong-bot

## Network

User Settings:
```json
  "http.systemCertificatesNode": true,
  "github.copilot.advanced.debug.useElectronFetcher": true,
  "github.copilot.advanced.debug.useNodeFetcher": false,
  "github.copilot.advanced.debug.useNodeFetchFetcher": true
```

Connecting to https://api.github.com:
- DNS ipv4 Lookup: 20.205.243.168 (4 ms)
- DNS ipv6 Lookup: Error (45 ms): getaddrinfo ENOTFOUND api.github.com
- Proxy URL: None (0 ms)
- Electron fetch (configured): HTTP 200 (57 ms)
- Node.js https: HTTP 200 (190 ms)
- Node.js fetch: HTTP 200 (67 ms)

Connecting to https://api.githubcopilot.com/_ping:
- DNS ipv4 Lookup: 140.82.114.22 (36 ms)
- DNS ipv6 Lookup: Error (27 ms): getaddrinfo ENOTFOUND api.githubcopilot.com
- Proxy URL: None (7 ms)
- Electron fetch (configured): HTTP 200 (309 ms)
- Node.js https: HTTP 200 (1163 ms)
- Node.js fetch: HTTP 200 (832 ms)

Connecting to https://copilot-proxy.githubusercontent.com/_ping:
- DNS ipv4 Lookup: 20.250.119.64 (29 ms)
- DNS ipv6 Lookup: Error (22 ms): getaddrinfo ENOTFOUND copilot-proxy.githubusercontent.com
- Proxy URL: None (9 ms)
- Electron fetch (configured): HTTP 200 (776 ms)
- Node.js https: HTTP 200 (715 ms)
- Node.js fetch: HTTP 200 (712 ms)

Connecting to https://mobile.events.data.microsoft.com: HTTP 404 (215 ms)
Connecting to https://dc.services.visualstudio.com: HTTP 404 (800 ms)
Connecting to https://copilot-telemetry.githubusercontent.com/_ping: HTTP 200 (910 ms)
Connecting to https://copilot-telemetry.githubusercontent.com/_ping: HTTP 200 (841 ms)
Connecting to https://default.exp-tas.com: HTTP 400 (238 ms)

Number of system certificates: 110

## Documentation

In corporate networks: [Troubleshooting firewall settings for GitHub Copilot](https://docs.github.com/en/copilot/troubleshooting-github-copilot/troubleshooting-firewall-settings-for-github-copilot).
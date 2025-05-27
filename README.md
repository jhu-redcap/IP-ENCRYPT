# @IP-ENCRYPT Action Tag

## Overview

Public surveys present an opportunity for fraudulent data, especially when compensation is involved. In these cases, multiple safeguards should be implemented to detect and prevent fraud (e.g., RECAPTCHA).

Identifying multiple submissions from a single IP address can assist in fraud detection. While there are legitimate reasons for multiple submissions from the same IP, it may also indicate suspicious behavior. In combination with other analyses, this can be valuable.

However, since IP addresses are personal identifiers, they are usually not collected. What matters is not the actual IP, but whether submissions originate from the same source.

### Introducing `@IP-ENCRYPT`

This action tag captures an **encrypted version** of the IP address. It protects the respondent's identity while allowing study teams to identify multiple submissions from the same source.

When applied to a **TEXT** field, the encrypted IP is saved when the instrument is completed in **Survey mode**.

## Notes

- The value is captured **only** on the initial entry of the survey.
    - If "Save and Return" is used from a different location later, the encrypted IP is **not updated**.
- If a **proxy server or VPN** is used, that server's IP is captured.
- It is recommended to use additional tags:
    - `@READONLY`
    - `@HIDDEN-SURVEY`
- If necessary and approved (e.g., by IRB), a **REDCap administrator** may decrypt a questionable IP address for further analysis.

## Implementation

1. Create a **Text** field on the survey instrument.
2. Set **Validation** to `---None---`.
3. Add the `@IP-ENCRYPT` action tag.
4. (Recommended) Also add:
    - `@READONLY`
    - `@HIDDEN-SURVEY`

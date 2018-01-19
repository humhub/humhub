# Markdown Cheatsheet

The following are supported Markdown syntax for the Social Networking Kit [HumHub](https://www.humhub.org)

### Headers
```
# This is an <h1> tag
## This is an <h2> tag
### This is an <h3> tag
#### This is an <h4> tag
##### This is an <h5> tag
###### This is an <h6> tag
```

### Blockquotes
```
As Kanye West said:

> We're living the future so
> the present is our past.
```

### Emphasis

#### Bold
```
**This text will be bold**
__This will also be bold__
```

#### Italic
```
*This text will be italic*
_This will also be italic_
```

> Note: `_You **can** combine both as well_`

### Code

#### Inline Code
```
I think you should use an
`<addr>` element here instead.
```

#### Syntax highlighting
```
\```javascript
function fancyAlert(arg) {
  if(arg) {
    $.facebox({div:'#foo'})
  }
}
```\
```

> Note: That all "`\`" must be removed and all "<code>```</code>" must be on its own line for Syntax highlighting to work!

### Lists

#### Unordered
```
* Item 1
* Item 2
  * Item 2a
  * Item 2b
```
#### Ordered
```
1. Item 1
2. Item 2
3. Item 3
   a. Item 3a
   b. Item 3b
```

### Links
```
http://github.com - automatic!
[GitHub](http://github.com)
```

### Images

#### Non-Linked Images
```
![GitHub Logo](/images/logo.png)
Format: ![Alt Text](image url) - Note: Must have a image file type or won't work.
```

#### Linked Images
```
[![Alt Text](/images/logo.png)](www.example.com) - Note: Must have a image file type or won't work.
```

### Tables
```
First Header | Second Header
------------ | -------------
Content from cell 1 | Content from cell 2
Content in the first column | Content in the second column
```

### Strikethrough
```
~~this~~
```

### Ignoring Markdown formatting
```
Let's rename \*our-new-project\* to \*our-old-project\*.
```

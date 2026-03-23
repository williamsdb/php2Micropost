<a name="readme-top"></a>


<!-- PROJECT LOGO -->
<br />
<div align="center">

<h3 align="center">php2Micropost</h3>

  <p align="center">
    A simple library that allows posting to WordPress Microposts via the API.
    <br />
  </p>
</div>



<!-- TABLE OF CONTENTS -->
<details>
  <summary>Table of Contents</summary>
  <ol>
    <li>
      <a href="#about-the-project">About The Project</a>
      <ul>
        <li><a href="#built-with">Built With</a></li>
      </ul>
    </li>
    <li>
      <a href="#getting-started">Getting Started</a>
      <ul>
        <li><a href="#prerequisites">Prerequisites</a></li>
        <li><a href="#installation">Installation</a></li>
      </ul>
    </li>
    <li><a href="#usage">Usage</a></li>
    <li><a href="#roadmap">Roadmap</a></li>
    <li><a href="#contributing">Contributing</a></li>
    <li><a href="#license">License</a></li>
    <li><a href="#contact">Contact</a></li>
    <li><a href="#acknowledgments">Acknowledgments</a></li>
  </ol>
</details>



<!-- ABOUT THE PROJECT -->
## About The Project

I have accounts on plenty of microblogging sites (Twitter, Mastodon, Bluesky and even Threads) but they are all owned and controlled by someone else. I decided that I wanted something that I could use and manage, and a quick search came up with [Michael Gbadebo](https://mothcloud.com/introducing-microposts-microblogging-for-wordpress/)'s WordPress plugin called [Microposts](https://wordpress.org/plugins/microposts/).

I installed this and after playing around for a while, I decided that it was just what I was looking for. However, it was quite cumbersome to make a post from a mobile device when I was out and about. What I wanted was a simple page from which I could post including an image so I built this connector.

Very much inspired by my [php2Micropost](https://github.com/williamsdb/php2Micropost/) project this package will allow you to easily integrate creating Microposts from your own code.

<a href='https://ko-fi.com/Y8Y0POEES' target='_blank'><img height='36' style='border:0px;height:36px;' src='https://storage.ko-fi.com/cdn/kofi5.png?v=6' border='0' alt='Buy Me a Coffee at ko-fi.com' /></a>

<p align="right">(<a href="#readme-top">back to top</a>)</p>



### Built With

* [PHP](https://php.net)
* [WordPress API](https://developer.wordpress.com/docs/api/)
* [Microposts plugin by Michael Gbadebo](https://wordpress.org/plugins/microposts/)

<p align="right">(<a href="#readme-top">back to top</a>)</p>



<!-- GETTING STARTED -->
## Getting Started

Running the script is very straightforward:

1. install [composer](https://getcomposer.org/)

3. add php2Micropost

> composer.phar require williamsdb/php2micropost

You can read more about how this all works in [these blog posts](https://www.spokenlikeageek.com/tag/microposts/).

### Prerequisites

Requirements are very simple; it requires the following:

1. PHP (I tested on v8.1.13)
2. WordPress (I tested on 6.9.4)
2. Michael Gbadebo's [Micropost plugin](https://wordpress.org/plugins/microposts/)
3. a WordPress blog and an Application Password (see [this post](https://www.spokenlikeageek.com/2023/08/02/exporting-all-wordpress-posts-to-pdf/) for details of how to do that).

### Installation

1. As above

<p align="right">(<a href="#readme-top">back to top</a>)</p>



<!-- USAGE EXAMPLES -->
## Usage

Here's a few examples to get you started. 

###  Setup and connect to WordPress

```php
require __DIR__ . '/vendor/autoload.php';

use williamsdb\php2micropost\php2Micropost;

$base_url = 'https://www.your-domain.com/wp-json/wp/v2';
$username = 'WordPress username with write permissions';
$password = 'Your 24-character Application Password';  
$parseURL = false;

$php2Micropost = new php2Micropost(
    base_url: $base_url,
    username: $username,
    password: $password,
    parseUrls: false,
);
$connection = $php2Micropost->wordpress_connect();
```

### Sending post with only text

Text sent can either be plain text or HTML. If you send plain text and $parseURLs is TRUE then the text will be checked for URLs and if any found they will be wrapped in ```<a href="URL">URL</a>```. If you are passing HTML then $parseURLs should be set to FALSE to retain the HTML.

```php
$text = "This is a test post from php2Micropost. " . date('Y-m-d H:i:s');

// post with text and nothing else
$response = $php2Micropost->post_to_wordpress(
    connection: $connection,
    text: $text,
    title: '',
    media: '',
);
```

### Uploading a post with a single image

Microposts only accepts a single image so that's all that is allowed here.

```php
$image = "/Users/neilthompson/Downloads/IMG_9547.jpeg";

$text = "This is a test post from php2Micropost. " . date('Y-m-d H:i:s');

// post with text and nothing else
$response = $php2Micropost->post_to_wordpress(
    connection: $connection,
    text: $text,
    title: '',
    media: $image,
);
```

### Adding tags to a post

If the tag already exists, it will be associated with the post; otherwise, it will be created.

```php
$tags = ['test', 'php2Micropost', 'reading'];

$text = "This is a test post from php2Micropost. " . date('Y-m-d H:i:s');

// post with text and nothing else
$response = $php2Micropost->post_to_wordpress(
    connection: $connection,
    text: $text,
    title: '',
    media: $image,
    tags: $tags,
);
```

<p align="right">(<a href="#readme-top">back to top</a>)</p>



<!-- ROADMAP -->
## Known Issues

See the [open issues](https://github.com/williamsdb/php2Micropost/issues) for a full list of proposed features (and known issues).

<p align="right">(<a href="#readme-top">back to top</a>)</p>



<!-- CONTRIBUTING -->
## Contributing

Contributions are what make the open source community such an amazing place to learn, inspire, and create. Any contributions you make are **greatly appreciated**.

If you have a suggestion that would make this better, please fork the repo and create a pull request. You can also simply open an issue with the tag "enhancement".
Don't forget to give the project a star! Thanks again!

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

<p align="right">(<a href="#readme-top">back to top</a>)</p>



<!-- LICENSE -->
## License

Distributed under the GNU General Public License v3.0. See `LICENSE` for more information.

<p align="right">(<a href="#readme-top">back to top</a>)</p>



<!-- CONTACT -->
## Contact

Bluesky - [@spokenlikeageek.com](https://bsky.app/profile/spokenlikeageek.com)

Mastodon - [@spokenlikeageek](https://techhub.social/@spokenlikeageek)

X - [@spokenlikeageek](https://x.com/spokenlikeageek) 

Website - [https://spokenlikeageek.com](https://www.spokenlikeageek.com/tag/microposts/)

Project link - [Github](https://github.com/williamsdb/php2Micropost)

<p align="right">(<a href="#readme-top">back to top</a>)</p>


<!-- ACKNOWLEDGMENTS -->
## Acknowledgments

* [WordPress Microposts](https://wordpress.org/plugins/microposts/)

<p align="right">(<a href="#readme-top">back to top</a>)</p>


[![](https://github.com/williamsdb/php2Micropost/graphs/contributors)](https://img.shields.io/github/contributors/williamsdb/php2Micropost.svg?style=for-the-badge)

![](https://img.shields.io/github/contributors/williamsdb/php2Micropost.svg?style=for-the-badge)
![](https://img.shields.io/github/forks/williamsdb/php2Micropost.svg?style=for-the-badge)
![](https://img.shields.io/github/stars/williamsdb/php2Micropost.svg?style=for-the-badge)
![](https://img.shields.io/github/issues/williamsdb/php2Micropost.svg?style=for-the-badge)
<!-- MARKDOWN LINKS & IMAGES -->
<!-- https://www.markdownguide.org/basic-syntax/#reference-style-links -->
[contributors-shield]: https://img.shields.io/github/contributors/williamsdb/php2Micropost.svg?style=for-the-badge
[contributors-url]: https://github.com/williamsdb/php2Micropost/graphs/contributors
[forks-shield]: https://img.shields.io/github/forks/williamsdb/php2Micropost.svg?style=for-the-badge
[forks-url]: https://github.com/williamsdb/php2Micropost/network/members
[stars-shield]: https://img.shields.io/github/stars/williamsdb/php2Micropost.svg?style=for-the-badge
[stars-url]: https://github.com/williamsdb/php2Micropost/stargazers
[issues-shield]: https://img.shields.io/github/issues/williamsdb/php2Micropost.svg?style=for-the-badge
[issues-url]: https://github.com/williamsdb/php2Micropost/issues
[license-shield]: https://img.shields.io/github/license/williamsdb/php2Micropost.svg?style=for-the-badge
[license-url]: https://github.com/williamsdb/php2Micropost/blob/master/LICENSE.txt
[linkedin-shield]: https://img.shields.io/badge/-LinkedIn-black.svg?style=for-the-badge&logo=linkedin&colorB=555
[linkedin-url]: https://linkedin.com/in/linkedin_username
[product-screenshot]: images/screenshot.png
[Next.js]: https://img.shields.io/badge/next.js-000000?style=for-the-badge&logo=nextdotjs&logoColor=white
[Next-url]: https://nextjs.org/
[React.js]: https://img.shields.io/badge/React-20232A?style=for-the-badge&logo=react&logoColor=61DAFB
[React-url]: https://reactjs.org/
[Vue.js]: https://img.shields.io/badge/Vue.js-35495E?style=for-the-badge&logo=vuedotjs&logoColor=4FC08D
[Vue-url]: https://vuejs.org/
[Angular.io]: https://img.shields.io/badge/Angular-DD0031?style=for-the-badge&logo=angular&logoColor=white
[Angular-url]: https://angular.io/
[Svelte.dev]: https://img.shields.io/badge/Svelte-4A4A55?style=for-the-badge&logo=svelte&logoColor=FF3E00
[Svelte-url]: https://svelte.dev/
[Laravel.com]: https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white
[Laravel-url]: https://laravel.com
[Bootstrap.com]: https://img.shields.io/badge/Bootstrap-563D7C?style=for-the-badge&logo=bootstrap&logoColor=white
[Bootstrap-url]: https://getbootstrap.com
[JQuery.com]: https://img.shields.io/badge/jQuery-0769AD?style=for-the-badge&logo=jquery&logoColor=white
[JQuery-url]: https://jquery.com 

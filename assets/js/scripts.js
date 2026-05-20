// 3rd party packages from NPM
import $ from 'jquery';


// Our modules/ classes
import BackTop from './modules/BackTop';
//import MobileNav from './modules/MobileNav';



// Instantiate a new object using our modules/classes
let backTop = new BackTop();
//const mobilenav = new MobileNav();

// Homepage banner video carousel.
// PHP renders the first video immediately for fast display, then stores the
// full ACF video list in data-banner-videos for this script to rotate through.
const initBannerVideoCarousel = () => {
  const banner = document.querySelector('.banner[data-banner-videos]');
  const video = banner ? banner.querySelector('.banner__video') : null;

  if (!banner || !video) {
    return;
  }

  let videos = [];

  try {
    // The data attribute is JSON encoded in header.php.
    videos = JSON.parse(banner.dataset.bannerVideos);
  } catch (error) {
    return;
  }

  if (!Array.isArray(videos) || videos.length === 0) {
    return;
  }

  video.muted = true;
  video.playsInline = true;

  if (videos.length === 1) {
    // Native looping is enough when editors upload only one banner video.
    video.loop = true;
    video.play().catch(() => {});
    return;
  }

  let currentVideo = 0;

  const playVideo = (index) => {
    const nextVideo = videos[index];

    if (!nextVideo || !nextVideo.src) {
      return;
    }

    video.src = nextVideo.src;

    // Poster images are optional per ACF repeater row.
    if (nextVideo.poster) {
      video.poster = nextVideo.poster;
    } else {
      video.removeAttribute('poster');
    }

    video.load();
    video.play().catch(() => {});
  };

  video.addEventListener('ended', () => {
    // Move forward one item and wrap back to the first video forever.
    currentVideo = (currentVideo + 1) % videos.length;
    playVideo(currentVideo);
  });

  video.play().catch(() => {});
};

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initBannerVideoCarousel);
} else {
  initBannerVideoCarousel();
}

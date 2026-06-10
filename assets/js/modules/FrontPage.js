class FrontPage {
  constructor() {
    this.initBannerVideoCarousel();
  }

  initBannerVideoCarousel() {
    const banner = document.querySelector('.banner[data-banner-videos]');
    const video = banner ? banner.querySelector('.banner__video') : null;
    const desktopMedia = window.matchMedia('(min-width: 50em)');

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

    let currentVideo = 0;
    let hasEndedListener = false;

    const unloadVideo = () => {
      video.pause();
      video.removeAttribute('src');
      video.load();
    };

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

    const enableDesktopVideo = () => {
      if (videos.length === 1) {
        // Native looping is enough when editors upload only one banner video.
        video.loop = true;
      } else if (!hasEndedListener) {
        video.addEventListener('ended', () => {
          // Move forward one item and wrap back to the first video forever.
          currentVideo = (currentVideo + 1) % videos.length;
          playVideo(currentVideo);
        });

        hasEndedListener = true;
      }

      playVideo(currentVideo);
    };

    const syncVideoToViewport = () => {
      if (desktopMedia.matches) {
        enableDesktopVideo();
        return;
      }

      unloadVideo();
    };

    syncVideoToViewport();

    if (desktopMedia.addEventListener) {
      desktopMedia.addEventListener('change', syncVideoToViewport);
    } else if (desktopMedia.addListener) {
      desktopMedia.addListener(syncVideoToViewport);
    }
  }

}

export default FrontPage;

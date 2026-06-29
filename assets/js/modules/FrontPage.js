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
    let isChangingVideo = false;

    const unloadVideo = () => {
      video.pause();
      video.removeAttribute('src');
      video.classList.remove('banner__video--is-visible');
      video.load();
    };

    const loadVideo = (index, revealOnLoad = true) => {
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

      const playWhenReady = () => {
        if (revealOnLoad) {
          video.classList.add('banner__video--is-visible');
        }

        video.play().catch(() => {});
        isChangingVideo = false;
      };

      if (video.readyState >= 2) {
        playWhenReady();
        return;
      }

      video.addEventListener('loadeddata', playWhenReady, { once: true });
    };

    const playVideo = (index) => {
      loadVideo(index);
    };

    const transitionToVideo = (index) => {
      if (isChangingVideo) {
        return;
      }

      isChangingVideo = true;
      video.classList.remove('banner__video--is-visible');

      window.setTimeout(() => {
        loadVideo(index);
      }, 260);
    };

    const enableDesktopVideo = () => {
      if (videos.length === 1) {
        // Native looping is enough when editors upload only one banner video.
        video.loop = true;
      } else if (!hasEndedListener) {
        video.addEventListener('ended', () => {
          // Move forward one item and wrap back to the first video forever.
          currentVideo = (currentVideo + 1) % videos.length;
          transitionToVideo(currentVideo);
        });

        hasEndedListener = true;
      }

      if (!video.src) {
        playVideo(currentVideo);
      }
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

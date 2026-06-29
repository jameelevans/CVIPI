// 3rd party packages from NPM
import $ from 'jquery';


// Our modules/ classes
import BackTop from './modules/BackTop';
import ContactFaqs from './modules/ContactFaqs';
import CvipiMap from './modules/CvipiMap';
import EventFilters from './modules/EventFilters';
import FrontPage from './modules/FrontPage';
import MobileNav from './modules/MobileNav';
import ResourceFilters from './modules/ResourceFilters';
import ShareButton from './modules/ShareButton';
import SiteAnimations from './modules/SiteAnimations';
import SuccessStoryFilters from './modules/SuccessStoryFilters';
import VideoLightbox from './modules/VideoLightbox';



// Instantiate a new object using our modules/classes
const siteAnimations = new SiteAnimations();
let backTop = new BackTop();
const contactFaqs = new ContactFaqs();
const cvipiMap = new CvipiMap();
const eventFilters = new EventFilters();
const frontPage = new FrontPage();
const mobilenav = new MobileNav();
const resourceFilters = new ResourceFilters();
const shareButton = new ShareButton();
const successStoryFilters = new SuccessStoryFilters();
const videoLightbox = new VideoLightbox();

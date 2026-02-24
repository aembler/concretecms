// Import the frontend foundation for themes.
import '@concretecms/bedrock/assets/bedrock/js/frontend';

// Feature support
//import '@concretecms/bedrock/assets/account/js/frontend';
import '@concretecms/bedrock/assets/desktop/js/frontend';
import '@concretecms/bedrock/assets/forms/js/frontend';
// I wish we could include this in Atomik but if we do it collides with the use of it in the core components.
//import '@concretecms/bedrock/assets/calendar/js/frontend';
import '@concretecms/bedrock/assets/navigation/js/frontend';
import '@concretecms/bedrock/assets/conversations/js/frontend';
import '@concretecms/bedrock/assets/imagery/js/frontend';

// I'm currently removing this line because it includes things from old bedrock, and I want to ensure
// that the core works _without_ old bedrock. Ideally in v10 we will rewrite all of these frontend
// components to use custom web components so we dont' have to worry about these kind of collisions
//import '@concretecms/bedrock/assets/documents/js/frontend';

// Custom feature support
import './features/imagery/hero-image/offset-title';
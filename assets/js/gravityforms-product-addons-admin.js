/*
* Gravity Forms Product Add-Ons Admin
*/

class GravityFormsProductAddonsAdmin {
    static instance;

    static register(settings) {
        if (!this.instance) {
            this.instance = new GravityFormsProductAddonsAdmin(settings);
        }

        return this.instance;
    }

    constructor(settings) {
        this.settings = settings;
        this.observeGFormAvailability();
    }

    observeGFormAvailability() {
        let mutationCount = 0;
        const maxMutations = 100; // Set a limit to avoid infinite loop

        const observer = new MutationObserver((mutations, obs) => {
            mutationCount += mutations.length;

            if (window.gform) {
                obs.disconnect(); // Disconnect observer once gform is available
                this.init();
            } else if (mutationCount > maxMutations) {
                obs.disconnect(); // Disconnect observer after maxMutations to avoid infinite loop
                console.warn('Stopped observing after reaching the mutation limit.');
            }
        });

        observer.observe(document, {
            childList: true,
            subtree: true
        });
    }

    init() {
        gform.addFilter('gform_merge_tags', this.addMergeTags.bind(this));
    }

    addMergeTags(mergeTags, elementId, hideAllFields, excludeFieldTypes, isPrepop, option) {

        const groups = this.settings.merge_tags;
        // Loop though the groups and add the tags to the mergeTags
        for (const index in groups) {
            const key = groups[index].key;
            const tags = groups[index].tags;
            const label = groups[index].label;

            const filtered_tags = tags.filter(tag => {
                const tag_allowed_on = tag.allowed_on || [];
                // If the elementId starts with field_default_value check if allowed on is 'field_default_value_*' or matches the elementId exactly.
                if (elementId.startsWith('field_default_value')) {
                    return tag_allowed_on.includes('field_default_value_*') || tag_allowed_on.includes(elementId);
                }

                // Otherwise check if the tag is allowed on the elementId or if it's allowed on all elements or if no allowed_on is set.
                if (tag_allowed_on.includes(elementId) || tag_allowed_on.includes('all') || tag_allowed_on.length === 0) {
                    return true;
                }
            });

            mergeTags[key] = {
                label: label,
                tags: filtered_tags
            };
        }

        return mergeTags; // Return the modified mergeTags
    }
}

// Instantiate the class to ensure the code is executed
GravityFormsProductAddonsAdmin.register(wc_gf_addons);

## Testimonials Pro

Lightweight WordPress plugin to collect, manage, and display customer testimonials. It offers a submission form via shortcode, an admin panel to moderate entries, and a responsive grid to showcase approved testimonials.

### Requirements
- WordPress 5.0+
- PHP 7.4+

### Key Features
- Collect testimonials from any page/post using a shortcode form
- Star ratings (1–5) with clickable UI
- Moderation workflow: pending → approve/reject → display
- Responsive testimonials grid with pagination
- Filter grid by rating (e.g., only 5-star)
- Customizable appearance (colors, grid columns, form width)
- Gutenberg/classic editor compatible
- Translation-ready (`testimonials-pro` text domain)

### Installation
1. Install the ZIP in WordPress:
   - WP Admin → Plugins → Add New → Upload Plugin
   - Select `testimonials-pro.zip` and activate
2. Upon activation, the plugin registers the `Testimonial` post type and prepares storage for submissions.

### Shortcodes
- Submission form:
```text
[testimonial_form]
```

- Testimonials grid (show all approved):
```text
[testimonial_grid]
```

- Grid with rating filter and pagination:
```text
[testimonial_grid view="5" total="3"]
```

Parameters for `testimonial_grid`:
- `view`: `1` | `2` | `3` | `4` | `5` | `all` (default `all`)
- `total`: Positive integer per page or `all` (default `all`)

Examples:
- Show all: `[testimonial_grid]`
- 3 per page: `[testimonial_grid total="3"]`
- Only 5-star, 2 per page: `[testimonial_grid view="5" total="2"]`

### How It Works
- Visitors submit testimonials via `[testimonial_form]`.
- Submissions are stored and marked as pending.
- Admins moderate submissions in WP Admin.
- Approved testimonials appear automatically in `[testimonial_grid]` based on your chosen filters.

### Admin: Managing Testimonials
- WP Admin → All Testimonials
  - Review details (name, email, title, rating, review, submitted date)
  - Approve or reject pending entries
  - Delete unwanted entries

### Settings: Appearance & Layout
WP Admin → Testimonials → Settings
- Button color and hover color (supports color picker and hex)
- Form background color
- Grid columns (desktop): 1–4
- Form width:
  - Fixed (default)
  - Container (full width)
  - Custom (e.g., `500px`, `80%`, `50vw`)

Changes apply automatically to pages rendering the form or grid shortcodes.

### Frontend Behavior
- Assets load only on pages that contain `[testimonial_form]` or `[testimonial_grid]`.
- Form includes a star-rating widget; clicking stars sets the hidden rating field.
- Grid is responsive, and columns adapt on smaller screens.

### Usage Tips
- Place `[testimonial_form]` on a dedicated “Testimonials” or “Leave a Review” page.
- Display approved testimonials on landing pages with `[testimonial_grid]`.
- Use `view` and `total` to curate what visitors see (e.g., highlight top ratings or paginate long lists).

### Localization
- Text domain: `testimonials-pro`
- `.pot` file included in `languages/`

### Security & Privacy
- Nonces protect form submissions from forgery.
- Inputs are sanitized and validated server-side.
- Avoid collecting unnecessary personal data. If you show emails publicly, ensure you have consent. By default, emails are used internally for moderation and not displayed on the grid.

### Compatibility Notes
- Works with most themes out of the box. If your theme has very opinionated CSS, add overrides or adjust plugin settings to match branding.

### Troubleshooting
- Grid shows “No testimonials found.”
  - Ensure at least one testimonial is approved.
  - Confirm the shortcode is placed on a published page.
- Submissions not appearing for moderation
  - Check the form page includes `[testimonial_form]`.
  - Confirm you’re logged in with sufficient permissions to see the admin page.
- Styles not applying
  - Clear caching plugins/CDN.
  - Ensure the page actually contains the relevant shortcode so assets enqueue.

### Developer Notes
- Custom post type: `testimonial`
- Shortcodes: `testimonial_form`, `testimonial_grid`
- Frontend assets in `assets/css` and `assets/js`
- Settings stored in `testimonials_pro_options`

This document focuses on usage and high-level structure and deliberately omits sensitive implementation details.

### Changelog
- 1.0.0 — Initial release

### License
- GPL v2 or later



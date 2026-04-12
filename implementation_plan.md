# Redesign Profile Page (Facebook-inspired Professional Look)

This plan outlines the steps to overhaul the profile page rendering to follow a "Facebook-style" professional layout: a prominent cover photo, a smaller overlapping profile picture, and a clean structured information feed.

## Proposed Changes

### [Style] [style.css](file:///c:/xampp/htdocs/4/View/assets/css/style.css)

Add "Social-Editorial" classes:
- **.profile-header**: Contains the cover photo and the overlapping profile picture.
- **.cover-photo**: Full-width gradient or image area.
- **.profile-avatar-wrapper**: Smaller, circular profile picture that overlaps the cover photo.
- **.profile-meta**: Name and Role positioned next to the avatar.
- **.profile-content-grid**: Sidebar (Bio/Info) on the left, Main Feed/Form on the right.
- **.info-card**: Individual cards for Email, Phone, and extra details.

### [UI] [view.js](file:///c:/xampp/htdocs/4/View/FrontOffice/view.js)

Update `renderProfile` to:
- Implement the **Cover Photo + Avatar overlap** header.
- Make the profile picture **smaller** (around 120-150px) and perfectly circular.
- Use a **Sidebar + Content** grid layout.
- The sidebar will contain "Intro" details (Bio, Joined Date).
- The main content will show the "About" section or the **refined Edit Form**.
- Add a "Joined Date" or other relevant details to fill out the Facebook feel.

## Verification Plan

### Manual Verification
- Log in as a user.
- Navigate to the Profile page.
- Verify the layout matches the professional "Editorial" look.
- Toggle "Edit Profile" and ensure the form is correctly styled and functional.
- Save changes and verify they reflect in the summary view.

-- Add a couple of test users (passwords are 'password' hashed with md5 just as a dummy or whatever format is used, but since it's dummy data, plain text or simple hash is fine. I'll use a dummy hash).
INSERT IGNORE INTO users (username, email, password_hash, role) VALUES 
('testuser1', 'test1@example.com', '$2y$10$dummyhashformypassword123', 'citizen'),
('testuser2', 'test2@example.com', '$2y$10$dummyhashformypassword123', 'citizen');

-- Get the user IDs (Assuming 1 is ilyes, but let's use subqueries to be safe)
SET @ilyes_id = (SELECT id FROM users WHERE username = 'ilyes' LIMIT 1);
SET @test1_id = (SELECT id FROM users WHERE username = 'testuser1' LIMIT 1);
SET @test2_id = (SELECT id FROM users WHERE username = 'testuser2' LIMIT 1);

-- Insert Forum Posts
INSERT INTO forum_posts (user_id, title, content, category, status) VALUES 
(@ilyes_id, 'Welcome to the new CivicPortal Forum!', 'Feel free to share your thoughts, report issues, or discuss upcoming events in our city.', 'General', 'pinned'),
(@test1_id, 'Issue with Transport Schedule', 'The bus on route 42 has been consistently delayed for the past three days. Can the transport department look into this?', 'Transport', 'open'),
(@test2_id, 'Community clean-up event this weekend', 'We are organizing a community clean-up in the central park this Saturday. Volunteers are welcome to join us!', 'Events', 'open'),
(@ilyes_id, 'Upcoming Town Hall Meeting', 'Join us next week for the town hall meeting where we will discuss the new budget allocation for public spaces.', 'Announcements', 'open'),
(@test1_id, 'Suggestions for new park amenities', 'I think we should add a dog park area to the east side of the park. What do you all think?', 'Suggestions', 'open');

-- Insert Forum Comments
-- Comments for Post 1 (Welcome)
SET @post1_id = (SELECT post_id FROM forum_posts WHERE title = 'Welcome to the new CivicPortal Forum!' LIMIT 1);
INSERT INTO forum_comments (post_id, user_id, content) VALUES 
(@post1_id, @test1_id, 'Thanks! Looking forward to using this platform. It looks great!'),
(@post1_id, @test2_id, 'Great initiative. Happy to be here.');

-- Comments for Post 2 (Transport)
SET @post2_id = (SELECT post_id FROM forum_posts WHERE title = 'Issue with Transport Schedule' LIMIT 1);
INSERT INTO forum_comments (post_id, user_id, content) VALUES 
(@post2_id, @test2_id, 'I have noticed this too. It usually happens around 8 AM.'),
(@post2_id, @ilyes_id, 'We are currently investigating the delays on route 42. Thank you for reporting this issue.');

-- Comments for Post 3 (Clean-up)
SET @post3_id = (SELECT post_id FROM forum_posts WHERE title = 'Community clean-up event this weekend' LIMIT 1);
INSERT INTO forum_comments (post_id, user_id, content) VALUES 
(@post3_id, @ilyes_id, 'I will definitely be there! What time does it start?'),
(@post3_id, @test1_id, 'Starts at 9 AM near the main fountain. See you there!');

-- Comments for Post 5 (Park)
SET @post5_id = (SELECT post_id FROM forum_posts WHERE title = 'Suggestions for new park amenities' LIMIT 1);
INSERT INTO forum_comments (post_id, user_id, content) VALUES 
(@post5_id, @test2_id, 'A dog park would be fantastic! I have to drive 20 mins to the nearest one right now.'),
(@post5_id, @ilyes_id, 'This is a great suggestion. I will add it to the agenda for the next town hall meeting.');
